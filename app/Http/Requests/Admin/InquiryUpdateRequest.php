<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class InquiryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('inquiry'));
    }

    public function rules(): array
    {
        return [
            'status' => [Rule::requiredIf(fn (): bool => $this->has('status')), Rule::enum(InquiryStatus::class)],
            'priority' => ['nullable', Rule::enum(InquiryPriority::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'declined_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
