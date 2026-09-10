<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            ? Gate::allows('update', $user)
            : Gate::allows('create', User::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $user = $this->route('user');
        $editing = $user instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                Rule::unique('users', 'email')->ignore($editing ? $user->id : null),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            // Optional on edit: leaving it blank keeps the current password
            // rather than clearing it.
            'password' => [
                $editing ? 'nullable' : 'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
            ],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'is_active' => 'account state',
        ];
    }
}
