<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class InquiryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('inquiry'));
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }
}
