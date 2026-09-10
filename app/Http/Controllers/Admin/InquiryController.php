<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Http\Requests\Admin\InquiryConversionRequest;
use App\Http\Requests\Admin\InquiryNoteRequest;
use App\Http\Requests\Admin\InquiryUpdateRequest;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\InquiryPipeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InquiryController extends AdminController
{
    private const SORTS = ['reference' => 'reference', 'name' => 'name', 'status' => 'status', 'priority' => 'priority', 'received' => 'created_at', 'updated' => 'updated_at'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inquiry::class);
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
            'priority' => $request->string('priority')->toString(),
            'assigned_to' => $request->string('assigned_to')->toString(),
        ];
        $query = Inquiry::query()->with(['assignee', 'client'])
            ->when($filters['q'] !== '', fn ($query) => $query->where(function ($query) use ($filters): void {
                $query->where('reference', 'like', '%'.$filters['q'].'%')->orWhere('name', 'like', '%'.$filters['q'].'%')->orWhere('company', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%');
            }))
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['priority'] !== '', fn ($query) => $query->where('priority', $filters['priority']))
            ->when($filters['assigned_to'] !== '', fn ($query) => $query->where('assigned_to', (int) $filters['assigned_to']));
        [$sort, $direction] = $this->applySort($query, self::SORTS, 'received', $request->string('sort')->toString(), $request->string('direction')->toString() ?: 'desc');

        return view('admin.inquiries.index', [
            'inquiries' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'assignees' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'counts' => $this->counts(),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry->load(['assignee', 'client', 'project', 'inquiryNotes.user', 'activityLogs.user']),
            'assignees' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => InquiryStatus::cases(),
            'priorities' => InquiryPriority::cases(),
        ]);
    }

    public function update(InquiryUpdateRequest $request, Inquiry $inquiry, InquiryPipeline $pipeline): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('assigned_to', $data)) {
            $pipeline->assign($inquiry, $data['assigned_to'] ? User::query()->findOrFail($data['assigned_to']) : null, $request->user());
        }
        if (isset($data['status'])) {
            $pipeline->transition($inquiry, InquiryStatus::from($data['status']), $data['declined_reason'] ?? null, $request->user());
        }
        if (array_key_exists('priority', $data)) {
            $inquiry->update(['priority' => $data['priority']]);
        }
        $message = "Inquiry {$inquiry->reference} updated.";

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $message, 'data' => ['status' => $inquiry->fresh()->status->value]]) : $this->saved('admin.inquiries.show', $message, [$inquiry]);
    }

    public function note(InquiryNoteRequest $request, Inquiry $inquiry, InquiryPipeline $pipeline): RedirectResponse|JsonResponse
    {
        $note = $pipeline->addNote($inquiry, $request->user(), $request->validated('body'));
        $message = 'Internal note added.';

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $message, 'data' => ['id' => $note->id]]) : $this->saved('admin.inquiries.show', $message, [$inquiry]);
    }

    public function convert(InquiryConversionRequest $request, Inquiry $inquiry, InquiryPipeline $pipeline): RedirectResponse|JsonResponse
    {
        $client = $pipeline->convert($inquiry, $request->user(), $request->validated('client_id'), $request->clientAttributes());
        $message = "Inquiry {$inquiry->reference} converted to {$client->name}.";

        return $request->expectsJson() ? response()->json(['ok' => true, 'message' => $message, 'data' => ['client_id' => $client->id]]) : $this->saved('admin.inquiries.show', $message, [$inquiry]);
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $counts = Inquiry::query()->toBase()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')->all();
        $result = ['all' => array_sum($counts)];
        foreach (InquiryStatus::cases() as $status) $result[$status->value] = (int) ($counts[$status->value] ?? 0);

        return $result;
    }
}
