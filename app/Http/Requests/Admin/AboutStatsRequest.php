<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\StudioStat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * The numbers the studio stands behind. Their own screen, because a
 * statistic is checked and changed on a different rhythm from the prose
 * around it — and because one page holding both was too long to read.
 */
final class AboutStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', new StudioStat());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'stats' => array_values(array_filter(
                (array) $this->input('stats', []),
                static function ($row): bool {
                    if (! is_array($row)) {
                        return false;
                    }

                    foreach (['value', 'suffix', 'label', 'note'] as $column) {
                        if (trim((string) ($row[$column] ?? '')) !== '') {
                            return true;
                        }
                    }

                    return false;
                },
            )),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'stats' => ['required', 'array', 'min:1', 'max:12'],
            'stats.*.id' => ['nullable', 'integer'],
            'stats.*.value' => ['required', 'string', 'max:16'],
            // Not every statistic carries one — "19 people" has no suffix —
            // so the field is present but may be empty.
            'stats.*.suffix' => ['present', 'nullable', 'string', 'max:8'],
            'stats.*.label' => ['required', 'string', 'max:60'],
            'stats.*.note' => ['required', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'stats.min' => 'Keep at least one statistic — the row is drawn from them.',
        ];
    }

    /** @return list<array<string, mixed>> */
    public function stats(): array
    {
        return array_values((array) $this->validated('stats', []));
    }
}
