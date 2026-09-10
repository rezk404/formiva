<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\StudioPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * The opening of the About area: who the studio is, and what it refuses.
 *
 * Framing prose lives in the settings singleton, positions in their own
 * table. Both are edited on one screen because they are one argument; they
 * are validated together here for the same reason.
 */
final class AboutOverviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', new StudioPosition());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'headline' => $this->trimmedList('headline'),
            'story' => $this->trimmedList('story'),
            'positions' => $this->nonEmptyRows('positions', ['index_label', 'title', 'body']),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'eyebrow' => ['required', 'string', 'max:60'],

            // Two lines, set as two lines. The public headline breaks between
            // them and italicises the second, so they are separate values
            // rather than one string with a newline in it.
            'headline' => ['required', 'array', 'size:2'],
            'headline.*' => ['required', 'string', 'max:120'],

            'story' => ['required', 'array', 'min:1', 'max:6'],
            'story.*' => ['required', 'string', 'max:1200'],

            'positions' => ['required', 'array', 'min:1', 'max:12'],
            'positions.*.id' => ['nullable', 'integer'],
            'positions.*.index_label' => ['required', 'string', 'max:8'],
            'positions.*.title' => ['required', 'string', 'max:160'],
            'positions.*.body' => ['required', 'string', 'max:600'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'headline.size' => 'The headline is set as exactly two lines.',
            'positions.min' => 'Keep at least one position — the chapter has no shape without one.',
        ];
    }

    /** @return list<array<string, mixed>> */
    public function positions(): array
    {
        return array_values((array) $this->validated('positions', []));
    }

    /** @return list<string> */
    private function trimmedList(string $key): array
    {
        return array_values(array_filter(
            array_map(static fn ($value): string => trim((string) $value), (array) $this->input($key, [])),
            static fn (string $value): bool => $value !== '',
        ));
    }

    /**
     * @param  list<string>  $columns
     * @return list<array<string, mixed>>
     */
    private function nonEmptyRows(string $key, array $columns): array
    {
        return array_values(array_filter(
            (array) $this->input($key, []),
            static function ($row) use ($columns): bool {
                if (! is_array($row)) {
                    return false;
                }

                foreach ($columns as $column) {
                    if (trim((string) ($row[$column] ?? '')) !== '') {
                        return true;
                    }
                }

                return false;
            },
        ));
    }
}
