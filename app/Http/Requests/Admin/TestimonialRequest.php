<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $testimonial = $this->route('testimonial');

        return $testimonial instanceof Testimonial
            ? Gate::allows('update', $testimonial)
            : Gate::allows('create', Testimonial::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'client_id' => $this->input('client_id') ?: null,
            'project_id' => $this->input('project_id') ?: null,
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'quote' => ['required', 'string', 'max:1000'],
            'author_name' => ['required', 'string', 'max:120'],
            'author_role' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:120'],
            'client_id' => ['nullable', Rule::exists('clients', 'id')->whereNull('deleted_at')],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'is_published' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'author_name' => 'name',
            'author_role' => 'role',
            'client_id' => 'client',
            'project_id' => 'project',
            'is_published' => 'publication state',
        ];
    }
}
