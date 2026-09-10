<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Support\Publishing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service
            ? Gate::allows('update', $service)
            : Gate::allows('create', Service::class);
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->string('slug')->trim()->toString();

        // The nested editor leaves an untouched blank row behind whenever
        // someone clicks "Add capability" and changes their mind. Dropping
        // wholly-empty rows here means validation still reports a row that
        // was half filled in, without inventing an error for one that was
        // never started.
        $items = array_values(array_filter(
            (array) $this->input('items', []),
            static fn ($item): bool => is_array($item) && trim(implode('', [
                (string) ($item['title'] ?? ''),
                (string) ($item['form'] ?? ''),
                (string) ($item['summary'] ?? ''),
            ])) !== '',
        ));

        $this->merge([
            'slug' => Str::slug($slug !== '' ? $slug : $this->string('title')->toString()),
            'items' => $items,
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'title' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('services', 'slug')->ignore($service instanceof Service ? $service->id : null),
            ],
            'index_label' => ['required', 'string', 'max:8'],
            // The geometric behaviour the shared 3D system adopts while this
            // pillar is open. Free text by design — resources/js/three reads
            // it as a key and falls back gracefully on an unknown one.
            'form' => ['required', 'string', 'max:40'],
            'lede' => ['required', 'string', 'max:400'],
            'why' => ['required', 'string', 'max:1200'],
            'outcome' => ['required', 'string', 'max:600'],
            'note' => ['required', 'string', 'max:300'],

            // Publishing state is not an editor field. It is changed through
            // the publishing bar, whose transitions the backend validates
            // against the record's current state — a dropdown here would be a
            // second, unvalidated way to say the same thing. On create there
            // is no record yet, so the two save buttons declare an intent.
            'intent' => ['nullable', Rule::in([Publishing::DRAFT, Publishing::PUBLISH])],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'items' => ['array', 'max:24'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.title' => ['required', 'string', 'max:120'],
            'items.*.form' => ['required', 'string', 'max:40'],
            'items.*.summary' => ['required', 'string', 'max:600'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'Another service already uses that slug.',
            'items.*.title.required' => 'Every capability needs a name — remove the row if it is not needed.',
            'items.*.form.required' => 'Every capability needs a form keyword.',
            'items.*.summary.required' => 'Every capability needs a one-line summary.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'index_label' => 'index',
            'lede' => 'lede',
            'why' => 'why it matters',
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
        ];
    }

    /**
     * The state a newly created service should start in. Ignored on update,
     * where the publishing bar owns the transition.
     */
    public function intent(): string
    {
        return (string) ($this->validated('intent') ?: Publishing::DRAFT);
    }

    /**
     * The capability rows, in submitted order.
     *
     * @return list<array<string, mixed>>
     */
    public function items(): array
    {
        return array_values((array) $this->validated('items', []));
    }
}
