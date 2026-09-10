<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ProcessStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * The prose around the process diagram — everything on the chapter that is
 * not a stage. Held in settings because there is exactly one of each.
 */
final class ProcessFramingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', new ProcessStage());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'headline' => array_values(array_filter(
                array_map(static fn ($value): string => trim((string) $value), (array) $this->input('headline', [])),
                static fn (string $value): bool => $value !== '',
            )),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'eyebrow' => ['required', 'string', 'max:60'],
            'headline' => ['required', 'array', 'size:2'],
            'headline.*' => ['required', 'string', 'max:120'],
            'lede' => ['required', 'string', 'max:400'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'headline.size' => 'The headline is set as exactly two lines.',
        ];
    }
}
