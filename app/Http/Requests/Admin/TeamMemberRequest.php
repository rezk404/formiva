<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\TeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class TeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('team_member');

        return $member instanceof TeamMember
            ? Gate::allows('update', $member)
            : Gate::allows('create', TeamMember::class);
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->string('slug')->trim()->toString();

        $this->merge([
            'slug' => Str::slug($slug !== '' ? $slug : $this->string('name')->toString()),
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $member = $this->route('team_member');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('team_members', 'slug')->ignore($member instanceof TeamMember ? $member->id : null),
            ],
            'role' => ['required', 'string', 'max:120'],
            'bio' => ['required', 'string', 'max:600'],
            'since_year' => ['required', 'integer', 'min:1990', 'max:'.(int) date('Y')],
            'email' => ['nullable', 'email:rfc', 'max:255'],

            // Portraits are drawn plates, not photographs. Leaving the seed
            // empty is normal: DatabaseContent derives a stable one from the
            // slug so the same person always gets the same artwork.
            'plate_seed' => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'plate_variant' => ['nullable', 'string', 'max:32'],
            'plate_ratio' => ['nullable', 'string', 'max:16'],
            'alt' => ['nullable', 'string', 'max:255'],

            'is_published' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'Another team member already uses that slug.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'since_year' => 'joined',
            'plate_seed' => 'plate seed',
            'plate_variant' => 'plate variant',
            'plate_ratio' => 'plate ratio',
            'alt' => 'portrait description',
            'is_published' => 'publication state',
        ];
    }
}
