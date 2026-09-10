<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category
            ? Gate::allows('update', $category)
            : Gate::allows('create', Category::class);
    }

    /**
     * A blank slug is a request to derive one, not an error — the field is
     * optional in the form and filled from the name here so validation sees
     * the value that will actually be stored.
     */
    protected function prepareForValidation(): void
    {
        $slug = $this->string('slug')->trim()->toString();

        $this->merge([
            'slug' => Str::slug($slug !== '' ? $slug : $this->string('name')->toString()),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'type' => ['required', Rule::enum(CategoryType::class)],
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Slugs are unique per type, matching the table's composite
                // key: "Systems" can name both a project family and a
                // journal category without colliding.
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query->where('type', $this->input('type')))
                    ->ignore($category instanceof Category ? $category->id : null),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'Another category of this type already uses that slug.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'type' => 'category type',
        ];
    }
}
