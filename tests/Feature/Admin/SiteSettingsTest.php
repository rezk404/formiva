<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Content\ContentImporter;
use App\Content\DatabaseContent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'brand_name' => 'FORMIVA',
            'brand_etymology' => 'FORM + VIVA',
            'brand_tagline' => 'Digital products. Business systems. One studio.',
            'brand_discipline' => 'Digital product & business systems studio',
            'brand_founded' => '2016',

            'meta_title' => 'FORMIVA — Digital products & business systems studio.',
            'meta_description' => 'FORMIVA designs and builds digital products and the systems behind them.',
            'meta_keywords' => 'digital product studio, business systems',
            'meta_locale' => 'en_GB',
            'meta_theme_color' => '#0D0D0C',

            'contact_email' => 'hello@formiva.studio',
            'contact_new_business' => 'new@formiva.studio',
            'contact_phone' => '+20 2 2461 0114',
            'contact_street' => 'Smart Village, Building B2',
            'contact_city' => 'Giza',
            'contact_postcode' => '12577',
            'contact_country' => 'Egypt',
            'contact_timezone' => 'EET',

            'social' => [
                ['label' => 'Instagram', 'handle' => '@formiva.studio', 'url' => 'https://instagram.com'],
            ],

            'legal_entity' => 'Formiva Studio',
            'legal_links' => [
                ['label' => 'Privacy', 'url' => '#'],
            ],
        ], $overrides);
    }

    public function test_the_screen_is_grouped_rather_than_a_key_value_table(): void
    {
        (new ContentImporter(resource_path('content')))->import();

        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('General')
            ->assertSee('SEO defaults')
            ->assertSee('Contact')
            ->assertSee('Social')
            ->assertSee('Legal')
            ->assertSee('hello@formiva.studio');
    }

    public function test_saving_rebuilds_the_whole_site_shape(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload())
            ->assertRedirect();

        $site = Setting::retrieve('content', 'site', []);

        $this->assertSame(['brand', 'meta', 'contact', 'social', 'legal'], array_keys($site));
        $this->assertSame(['name', 'etymology', 'tagline', 'discipline', 'founded'], array_keys($site['brand']));
        $this->assertSame(['title', 'description', 'keywords', 'locale', 'theme_color'], array_keys($site['meta']));
        $this->assertSame(['email', 'new_business', 'phone', 'street', 'city', 'postcode', 'country', 'timezone'], array_keys($site['contact']));
        $this->assertSame(['label', 'handle', 'url'], array_keys($site['social'][0]));
        $this->assertSame(['entity', 'links'], array_keys($site['legal']));
        $this->assertSame('FORMIVA', $site['brand']['name']);
    }

    public function test_the_public_contract_reads_the_saved_record(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload(['brand_tagline' => 'A new line.']))
            ->assertRedirect();

        $this->assertSame('A new line.', (new DatabaseContent())->site()['brand']['tagline']);
    }

    public function test_a_bad_theme_colour_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload(['meta_theme_color' => 'charcoal']))
            ->assertSessionHasErrors('meta_theme_color');
    }

    public function test_a_bad_social_url_is_refused_without_destroying_the_record(): void
    {
        (new ContentImporter(resource_path('content')))->import();
        $before = Setting::retrieve('content', 'site', []);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->payload([
                'social' => [['label' => 'Instagram', 'handle' => '@formiva', 'url' => 'not a url']],
            ]))
            ->assertSessionHasErrors('social.0.url');

        $this->assertSame($before, Setting::retrieve('content', 'site', []));
    }

    public function test_missing_required_values_are_refused(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [])
            ->assertSessionHasErrors(['brand_name', 'meta_title', 'contact_email', 'legal_entity']);
    }

    public function test_an_editor_cannot_reach_or_change_settings(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.settings.update'), $this->payload())->assertForbidden();
    }
}
