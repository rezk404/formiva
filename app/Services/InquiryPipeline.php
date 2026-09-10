<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Enums\ClientStatus;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class InquiryPipeline
{
    public function create(array $attributes): Inquiry
    {
        $inquiry = DB::transaction(function () use ($attributes): Inquiry {
            $inquiry = Inquiry::query()->create([
                ...$attributes,
                'reference' => $this->reference(),
                'status' => InquiryStatus::New,
                'priority' => InquiryPriority::Normal,
            ]);
            $this->activity($inquiry, ActivityEvent::Created, 'Inquiry received.', null, null);

            return $inquiry;
        });

        return $inquiry;
    }

    public function transition(Inquiry $inquiry, InquiryStatus $target, ?string $reason = null, ?User $actor = null): Inquiry
    {
        $current = $inquiry->status;
        if ($current === $target) {
            return $inquiry;
        }

        $allowed = match ($current) {
            InquiryStatus::New => [InquiryStatus::Reviewing, InquiryStatus::Declined, InquiryStatus::Spam],
            InquiryStatus::Reviewing => [InquiryStatus::Qualified, InquiryStatus::Declined, InquiryStatus::Spam],
            InquiryStatus::Qualified => [InquiryStatus::Converted, InquiryStatus::Declined, InquiryStatus::Spam],
            InquiryStatus::Converted => [],
            InquiryStatus::Declined, InquiryStatus::Spam => [InquiryStatus::Reviewing],
        };

        if (! in_array($target, $allowed, true)) {
            throw new UnprocessableEntityHttpException('That inquiry transition is not allowed.');
        }
        if ($target === InquiryStatus::Converted) {
            throw new UnprocessableEntityHttpException('Convert the inquiry through the client conversion action.');
        }
        if ($target === InquiryStatus::Declined && trim((string) $reason) === '') {
            throw new UnprocessableEntityHttpException('A decline reason is required.');
        }

        DB::transaction(function () use ($inquiry, $current, $target, $reason, $actor): void {
            $attributes = ['status' => $target];
            if ($target === InquiryStatus::Reviewing) $attributes['reviewed_at'] ??= $inquiry->reviewed_at ?? now();
            if ($target === InquiryStatus::Qualified) $attributes['qualified_at'] ??= $inquiry->qualified_at ?? now();
            if ($target === InquiryStatus::Declined) $attributes['declined_reason'] = trim((string) $reason);
            $inquiry->update($attributes);
            $event = match ($target) {
                InquiryStatus::Converted => ActivityEvent::Converted,
                InquiryStatus::Declined => ActivityEvent::Declined,
                InquiryStatus::Spam => ActivityEvent::MarkedSpam,
                default => ActivityEvent::StatusChanged,
            };
            $this->activity($inquiry, $event, sprintf('Status changed from %s to %s.', $current->value, $target->value), $actor, ['from' => $current->value, 'to' => $target->value]);
        });

        return $inquiry->refresh();
    }

    public function assign(Inquiry $inquiry, ?User $assignee, ?User $actor = null): Inquiry
    {
        $inquiry->update(['assigned_to' => $assignee?->id]);
        $this->activity($inquiry, ActivityEvent::Assigned, $assignee ? "Assigned to {$assignee->name}." : 'Assignment cleared.', $actor, ['assigned_to' => $assignee?->id]);

        return $inquiry->refresh();
    }

    public function addNote(Inquiry $inquiry, User $author, string $body): Model
    {
        $note = $inquiry->inquiryNotes()->create(['user_id' => $author->id, 'body' => trim($body)]);
        $this->activity($inquiry, ActivityEvent::NoteAdded, 'Internal note added.', $author, ['note_id' => $note->id]);

        return $note->load('user');
    }

    public function convert(Inquiry $inquiry, User $actor, ?int $clientId = null, ?array $client = null): Client
    {
        if ($inquiry->status === InquiryStatus::Converted && $inquiry->client_id) {
            return $inquiry->client;
        }
        if ($inquiry->status !== InquiryStatus::Qualified) {
            throw new UnprocessableEntityHttpException('Only qualified inquiries can be converted.');
        }

        return DB::transaction(function () use ($inquiry, $actor, $clientId, $client): Client {
            $record = $clientId ? Client::query()->findOrFail($clientId) : null;
            if (! $record) {
                $name = trim((string) ($client['name'] ?? $inquiry->company ?: $inquiry->name));
                $record = Client::query()->whereRaw('lower(name) = ?', [Str::lower($name)])->first();
                $record ??= Client::query()->create([
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
                    'wordmark' => Str::upper(Str::substr($name, 0, 12)),
                    'sector' => $inquiry->industry ?: 'Unspecified',
                    'country' => $inquiry->country,
                    'status' => ClientStatus::Prospect,
                    'source_inquiry_id' => $inquiry->id,
                    'notes' => 'Created from inquiry '.$inquiry->reference.'.',
                ]);
            }
            if (! $record->contacts()->where('email', $inquiry->email)->exists()) {
                $record->contacts()->create(['name' => $inquiry->name, 'email' => $inquiry->email, 'phone' => $inquiry->phone, 'is_primary' => $record->contacts()->count() === 0]);
            }
            $inquiry->update(['client_id' => $record->id, 'status' => InquiryStatus::Converted, 'converted_at' => $inquiry->converted_at ?? now()]);
            $this->activity($inquiry, ActivityEvent::Converted, "Converted to client {$record->name}.", $actor, ['client_id' => $record->id]);

            return $record;
        });
    }

    private function reference(): string
    {
        do {
            $reference = 'FM-'.str_pad((string) ((int) Inquiry::query()->max('id') + 1), 4, '0', STR_PAD_LEFT);
            if (Inquiry::query()->where('reference', $reference)->exists()) $reference = 'FM-'.strtoupper(Str::random(8));
        } while (Inquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function activity(Inquiry $inquiry, ActivityEvent $event, string $description, ?User $actor = null, ?array $properties = null): void
    {
        ActivityLog::query()->create([
            'user_id' => $actor?->id,
            'subject_type' => $inquiry->getMorphClass(),
            'subject_id' => $inquiry->id,
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
