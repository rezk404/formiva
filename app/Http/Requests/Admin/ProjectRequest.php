<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Project;
use App\Support\Publishing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            ? Gate::allows('update', $project)
            : Gate::allows('create', Project::class);
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->string('slug')->trim()->toString();

        $this->merge([
            'slug' => Str::slug($slug !== '' ? $slug : $this->string('name')->toString()),
            'disciplines' => $this->csv($this->input('disciplines')),
            'stack' => $this->csv($this->input('stack')),
            'gallery_media' => array_values(array_filter((array) $this->input('gallery_media', []), 'is_numeric')),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('projects', 'slug')->ignore($project instanceof Project ? $project->id : null)],
            'index_label' => ['required', 'string', 'max:8'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'project')],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'year' => ['required', 'integer', 'between:1900,2200'],
            'statement' => ['required', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:5000'],
            'challenge' => ['required', 'string', 'max:5000'],
            'solution' => ['required', 'string', 'max:5000'],
            'outcome' => ['required', 'string', 'max:5000'],
            'result_value' => ['nullable', 'string', 'max:80'],
            'result_label' => ['nullable', 'string', 'max:160'],
            'stage' => ['required', Rule::enum(\App\Enums\ProjectStage::class)],
            'services' => ['array', 'max:24'],
            'services.*' => ['integer', 'distinct', 'exists:services,id'],
            'disciplines' => ['array', 'max:24'],
            'disciplines.*' => ['string', 'max:80'],
            'stack' => ['array', 'max:24'],
            'stack.*' => ['string', 'max:80'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'alt' => ['required', 'string', 'max:255'],
            'plate_seed' => ['required', 'integer', 'between:0,2147483647'],
            'plate_variant' => ['required', 'string', 'max:40'],
            'plate_ratio' => ['required', 'string', 'max:20'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'intent' => ['nullable', Rule::in([Publishing::DRAFT, Publishing::PUBLISH])],
            'gallery_media' => ['array', 'max:24'],
            'gallery_media.*' => ['integer', 'distinct', 'exists:media,id'],
        ];
    }

    public function intent(): string
    {
        return (string) ($this->validated('intent') ?: Publishing::DRAFT);
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        return $this->safe()->except(['intent', 'gallery_media', 'services']);
    }

    /** @return list<int> */
    public function galleryMedia(): array
    {
        return array_map('intval', $this->validated('gallery_media', []));
    }

    /** @return list<int> */
    public function services(): array
    {
        return array_map('intval', $this->validated('services', []));
    }

    /** @return list<string> */
    private function csv(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(static fn ($item): string => trim((string) $item), $value)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
