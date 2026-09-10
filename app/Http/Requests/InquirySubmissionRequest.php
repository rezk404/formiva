<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InquirySubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $types = collect(config('formiva.intake.projectTypes', []));
        $type = $types->firstWhere('value', $this->input('type'));

        $this->merge([
            'project_type' => $type['value'] ?? null,
            'project_group' => $type['group'] ?? null,
            'budget_range' => $this->input('budget_choice') ?: $this->input('budget'),
            'services' => array_values(array_filter((array) $this->input('services', []), 'is_string')),
            'utm' => is_array($this->input('utm')) ? $this->input('utm') : [],
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $types = collect(config('formiva.intake.projectTypes', []))->pluck('value')->all();
        $groups = collect(config('formiva.intake.projectTypes', []))->pluck('group')->unique()->values()->all();
        $services = config('formiva.intake.services', []);
        $sizes = config('formiva.intake.companySizes', []);
        $timelines = config('formiva.intake.timelines', []);
        $budgets = collect(config('formiva.intake.budgets', []))->flatten()->unique()->values()->all();

        return [
            'name' => ['required', 'string', 'max:160'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'country' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in($types)],
            'project_type' => ['required', Rule::in($types)],
            'project_group' => ['required', Rule::in($groups)],
            'industry' => ['required', 'string', 'max:160'],
            'company_size' => ['required', Rule::in($sizes)],
            'problem' => ['required', 'string', 'max:5000'],
            'services' => ['array', 'max:24'],
            'services.*' => ['string', Rule::in($services)],
            'scope' => ['nullable', 'string', 'max:5000'],
            'budget' => ['nullable', 'string', 'max:120'],
            'budget_choice' => ['nullable', 'string', Rule::in($budgets)],
            'budget_range' => ['nullable', 'string', Rule::in($budgets)],
            'timeline' => ['required', Rule::in($timelines)],
            'message' => ['required', 'string', 'max:8000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'utm' => ['array'],
            'utm.*' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    /** @return array<string, mixed> */
    public function inquiryAttributes(): array
    {
        return [
            'kind' => 'project',
            'name' => $this->validated('name'),
            'company' => $this->validated('company'),
            'email' => $this->validated('email'),
            'phone' => $this->validated('phone'),
            'country' => $this->validated('country'),
            'project_type' => $this->validated('project_type'),
            'project_group' => $this->validated('project_group'),
            'industry' => $this->validated('industry'),
            'company_size' => $this->validated('company_size'),
            'problem' => $this->validated('problem'),
            'services' => $this->validated('services', []),
            'scope' => $this->validated('scope'),
            'budget_range' => $this->validated('budget_range'),
            'timeline' => $this->validated('timeline'),
            'message' => $this->validated('message'),
            'notes' => $this->validated('notes'),
            'source' => 'website',
            'utm' => $this->validated('utm', []),
        ];
    }
}
