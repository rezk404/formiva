<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CategoryType;
use App\Models\Insight;
use App\Support\Publishing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class InsightRequest extends FormRequest
{
    public function authorize(): bool
    {
        $insight = $this->route('insight');

        return $insight instanceof Insight
            ? Gate::allows('update', $insight)
            : Gate::allows('create', Insight::class);
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->string('slug')->trim()->toString();

        $this->merge([
            'slug' => Str::slug($slug !== '' ? $slug : $this->string('title')->toString()),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $insight = $this->route('insight');

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'required', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('insights', 'slug')->ignore($insight instanceof Insight ? $insight->id : null),
            ],
            'index_label' => ['required', 'string', 'max:8'],
            'dek' => ['required', 'string', 'max:400'],
            // Plain prose. The public entry renders it as a single escaped
            // paragraph — see resources/views/pages/insight.blade.php — so a
            // markup editor here would only ship tags to the reader.
            'body' => ['required', 'string', 'max:20000'],
            'reading_minutes' => ['nullable', 'integer', 'min:1', 'max:255'],

            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('type', CategoryType::Insight->value),
                ),
            ],
            'author_id' => ['nullable', Rule::exists('users', 'id')->whereNull('deleted_at')],

            'plate_seed' => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'plate_variant' => ['required', 'string', 'max:32'],
            'plate_ratio' => ['required', 'string', 'max:16'],
            'alt' => ['required', 'string', 'max:255'],

            // See ServiceRequest: publishing state is the publishing bar's,
            // not the editor's. On create the two save buttons declare it.
            'intent' => ['nullable', Rule::in([Publishing::DRAFT, Publishing::PUBLISH])],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'Another entry already uses that slug.',
            'category_id.exists' => 'Choose a category from the Insight list.',
            'alt.required' => 'Describe the cover plate — it is read aloud in place of the artwork.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'index_label' => 'index',
            'dek' => 'standfirst',
            'category_id' => 'category',
            'author_id' => 'author',
            'plate_seed' => 'plate seed',
            'plate_variant' => 'plate variant',
            'plate_ratio' => 'plate ratio',
            'alt' => 'cover description',
            'reading_minutes' => 'reading time',
        ];
    }

    /**
     * The state a newly created entry should start in. Ignored on update,
     * where the publishing bar owns the transition.
     */
    public function intent(): string
    {
        return (string) ($this->validated('intent') ?: Publishing::DRAFT);
    }
}
