<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\IntakeOptionKind;
use App\Models\IntakeOption;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class IntakeOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('intake_option');

        return $option instanceof IntakeOption
            ? Gate::allows('update', $option)
            : Gate::allows('create', IntakeOption::class);
    }

    protected function prepareForValidation(): void
    {
        $kind = IntakeOptionKind::tryFrom((string) $this->input('kind'));

        // Only project types and budgets are keyed to a group; the rest are
        // single lists and take the shared one, so the form never asks.
        $group = $kind !== null && $kind->usesGroups()
            ? Str::slug((string) $this->input('group'))
            : IntakeOption::SHARED_GROUP;

        $value = $this->string('value')->trim()->toString();

        if ($value === '') {
            $value = $this->string('label')->toString();
        }

        $this->merge([
            'group' => $group,
            'value' => $kind === IntakeOptionKind::Budget ? $value : Str::slug($value),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $option = $this->route('intake_option');
        $kind = IntakeOptionKind::tryFrom((string) $this->input('kind'));
        $group = (string) $this->input('group', IntakeOption::SHARED_GROUP);

        return [
            'kind' => ['required', Rule::enum(IntakeOptionKind::class)],
            'group' => ['required', 'string', 'max:60'],
            'label' => ['required', 'string', 'max:120'],
            'value' => [
                'required', 'string', 'max:120',
                // Uniqueness is checked against the value as it will be
                // stored, which for budgets carries the group as a prefix —
                // a plain unique rule on the raw input would refuse a range
                // that is legitimately offered under two project types.
                function (string $attribute, mixed $value, Closure $fail) use ($option, $kind, $group): void {
                    if ($kind === null) {
                        return;
                    }

                    $taken = IntakeOption::query()
                        ->where('kind', $kind)
                        ->where('value', IntakeOption::qualifiedValue($kind, $group, (string) $value))
                        ->when($option instanceof IntakeOption, fn ($query) => $query->whereKeyNot($option->id))
                        ->exists();

                    if ($taken) {
                        $fail('Another option in this step already stores that value.');
                    }
                },
            ],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'group.required' => 'Choose the project-type group this option belongs to.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'kind' => 'intake step',
            'is_active' => 'availability',
        ];
    }

    /**
     * The value as it is stored — budgets carry their group as a prefix.
     * Kept out of prepareForValidation so the uniqueness rule and the write
     * agree on one string.
     */
    public function storedValue(): string
    {
        $kind = IntakeOptionKind::from((string) $this->validated('kind'));

        return IntakeOption::qualifiedValue(
            $kind,
            (string) $this->validated('group'),
            (string) $this->validated('value'),
        );
    }
}
