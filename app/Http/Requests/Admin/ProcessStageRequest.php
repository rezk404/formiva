<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ProcessStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class ProcessStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $stage = $this->route('stage');

        return $stage instanceof ProcessStage
            ? Gate::allows('update', $stage)
            : Gate::allows('create', ProcessStage::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'overlap' => $this->boolean('overlap'),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'index_label' => ['required', 'string', 'max:8'],
            'title' => ['required', 'string', 'max:120'],
            'window' => ['required', 'string', 'max:60'],
            'body' => ['required', 'string', 'max:1000'],
            'output' => ['required', 'string', 'max:200'],

            // span_start / span_end place the stage on the drawn timeline,
            // as percentages of the whole engagement. weight sets how much
            // vertical mass its marker carries.
            'span_start' => ['required', 'integer', 'min:0', 'max:100'],
            'span_end' => ['required', 'integer', 'min:0', 'max:100', 'gte:span_start'],
            'weight' => ['required', 'numeric', 'min:0.05', 'max:9.99'],
            'overlap' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'span_end.gte' => 'A stage cannot finish before it starts.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'index_label' => 'index',
            'window' => 'time window',
            'span_start' => 'timeline start',
            'span_end' => 'timeline end',
        ];
    }
}
