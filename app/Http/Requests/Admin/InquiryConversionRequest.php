<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class InquiryConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('inquiry'));
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'client_name' => ['nullable', 'string', 'max:160'],
        ];
    }

    public function clientAttributes(): ?array
    {
        return $this->filled('client_name') ? ['name' => $this->validated('client_name')] : null;
    }
}
