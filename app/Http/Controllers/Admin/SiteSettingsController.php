<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SettingType;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The site singleton, presented as five groups rather than a key/value table.
 *
 * `content.site` is one nested array read by every public template, so the
 * risk is not a wrong value but a missing branch. The form is built from the
 * current array with defaults filled in, and the request rebuilds the whole
 * shape on save — an edit can never leave the array half-formed.
 */
final class SiteSettingsController extends AdminController
{
    public function edit(): View
    {
        $this->authorize('viewAny', Setting::class);

        $site = (array) Setting::retrieve('content', 'site', []);

        return view('admin.settings.edit', [
            'brand' => $this->section($site, 'brand', ['name', 'etymology', 'tagline', 'discipline', 'founded']),
            'meta' => $this->section($site, 'meta', ['title', 'description', 'keywords', 'locale', 'theme_color']),
            'contact' => $this->section($site, 'contact', ['email', 'new_business', 'phone', 'street', 'city', 'postcode', 'country', 'timezone']),
            'social' => array_values((array) ($site['social'] ?? [])),
            'legal' => [
                'entity' => (string) ($site['legal']['entity'] ?? ''),
                'links' => array_values((array) ($site['legal']['links'] ?? [])),
            ],
        ]);
    }

    public function update(SiteSettingsRequest $request): RedirectResponse
    {
        Setting::put('content', 'site', $request->toSiteArray(), SettingType::Json);

        return $this->saved('admin.settings.edit', 'Site settings saved.');
    }

    /**
     * One group of the array, with every expected key present as a string so
     * the form never renders an undefined index.
     *
     * @param  array<string, mixed>  $site
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private function section(array $site, string $group, array $keys): array
    {
        $values = (array) ($site[$group] ?? []);
        $shape = [];

        foreach ($keys as $key) {
            $shape[$key] = (string) ($values[$key] ?? '');
        }

        return $shape;
    }
}
