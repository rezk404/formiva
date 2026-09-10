<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Publishing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * One publishing transition, requested from a list row or an editor.
 *
 * The UI only renders the transitions a record can actually accept, but the
 * request re-checks anyway — a stale tab is the ordinary way an invalid
 * transition arrives.
 */
final class PublishRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The same request serves services and insights; whichever the route
        // bound is the record whose policy decides.
        $record = $this->route('service') ?? $this->route('insight');

        return $record !== null && Gate::allows('update', $record);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(Publishing::actions())],
            'published_at' => ['nullable', 'required_if:action,schedule', 'date', 'after:now'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'published_at.required_if' => 'Choose the date and time this should go live.',
            'published_at.after' => 'A scheduled date has to be in the future.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'published_at' => 'publish date',
        ];
    }

    public function action(): string
    {
        return (string) $this->validated('action');
    }

    public function scheduledFor(): ?Carbon
    {
        $value = $this->validated('published_at');

        return $value ? Carbon::parse((string) $value) : null;
    }
}
