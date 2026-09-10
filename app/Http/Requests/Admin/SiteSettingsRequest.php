<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * The site singleton.
 *
 * `content.site` is one nested array consumed by every public template and
 * by the layout's meta block, so the danger here is not a bad value — it is
 * a missing branch. Validation is written to guarantee the whole shape comes
 * back, and the controller rebuilds the array key by key rather than merging
 * whatever the form happened to send.
 */
final class SiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', new Setting());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'social' => $this->rows('social', ['label', 'handle', 'url']),
            'legal_links' => $this->rows('legal_links', ['label', 'url']),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'max:60'],
            'brand_etymology' => ['required', 'string', 'max:60'],
            'brand_tagline' => ['required', 'string', 'max:160'],
            'brand_discipline' => ['required', 'string', 'max:160'],
            'brand_founded' => ['required', 'string', 'max:8'],

            'meta_title' => ['required', 'string', 'max:180'],
            'meta_description' => ['required', 'string', 'max:400'],
            'meta_keywords' => ['required', 'string', 'max:500'],
            'meta_locale' => ['required', 'string', 'max:12'],
            'meta_theme_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'contact_email' => ['required', 'email:rfc', 'max:120'],
            'contact_new_business' => ['required', 'email:rfc', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:40'],
            'contact_street' => ['required', 'string', 'max:120'],
            'contact_city' => ['required', 'string', 'max:60'],
            'contact_postcode' => ['required', 'string', 'max:20'],
            'contact_country' => ['required', 'string', 'max:60'],
            'contact_timezone' => ['required', 'string', 'max:20'],

            'social' => ['array', 'max:12'],
            'social.*.label' => ['required', 'string', 'max:40'],
            'social.*.handle' => ['required', 'string', 'max:60'],
            'social.*.url' => ['required', 'url', 'max:255'],

            'legal_entity' => ['required', 'string', 'max:120'],
            'legal_links' => ['array', 'max:12'],
            'legal_links.*.label' => ['required', 'string', 'max:40'],
            // Placeholder hashes are legitimate here until the pages exist.
            'legal_links.*.url' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'meta_theme_color.regex' => 'Use a six-digit hex colour, for example #0D0D0C.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'brand_name' => 'studio name',
            'brand_etymology' => 'etymology',
            'brand_tagline' => 'tagline',
            'brand_discipline' => 'discipline',
            'brand_founded' => 'founded',
            'meta_title' => 'default title',
            'meta_description' => 'default description',
            'meta_keywords' => 'keywords',
            'meta_locale' => 'locale',
            'meta_theme_color' => 'theme colour',
            'contact_email' => 'general email',
            'contact_new_business' => 'new business email',
            'legal_entity' => 'legal entity',
        ];
    }

    /**
     * The exact array shape resources/content/site.php declares and every
     * public template reads. Rebuilt rather than merged, so a field removed
     * from the form cannot silently leave a stale value behind.
     *
     * @return array<string, mixed>
     */
    public function toSiteArray(): array
    {
        return [
            'brand' => [
                'name' => $this->validated('brand_name'),
                'etymology' => $this->validated('brand_etymology'),
                'tagline' => $this->validated('brand_tagline'),
                'discipline' => $this->validated('brand_discipline'),
                'founded' => $this->validated('brand_founded'),
            ],
            'meta' => [
                'title' => $this->validated('meta_title'),
                'description' => $this->validated('meta_description'),
                'keywords' => $this->validated('meta_keywords'),
                'locale' => $this->validated('meta_locale'),
                'theme_color' => $this->validated('meta_theme_color'),
            ],
            'contact' => [
                'email' => $this->validated('contact_email'),
                'new_business' => $this->validated('contact_new_business'),
                'phone' => $this->validated('contact_phone'),
                'street' => $this->validated('contact_street'),
                'city' => $this->validated('contact_city'),
                'postcode' => $this->validated('contact_postcode'),
                'country' => $this->validated('contact_country'),
                'timezone' => $this->validated('contact_timezone'),
            ],
            'social' => array_values(array_map(static fn (array $row): array => [
                'label' => $row['label'],
                'handle' => $row['handle'],
                'url' => $row['url'],
            ], (array) $this->validated('social', []))),
            'legal' => [
                'entity' => $this->validated('legal_entity'),
                'links' => array_values(array_map(static fn (array $row): array => [
                    'label' => $row['label'],
                    'url' => $row['url'],
                ], (array) $this->validated('legal_links', []))),
            ],
        ];
    }

    /**
     * @param  list<string>  $columns
     * @return list<array<string, mixed>>
     */
    private function rows(string $key, array $columns): array
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
