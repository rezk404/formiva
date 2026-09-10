<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The line between an editor and an admin.
 *
 * Content is the editor's; configuration and accounts are not. These tests
 * pin that boundary at the routes rather than at the policies, because a
 * route that forgot to ask is exactly the failure a policy test cannot see.
 */
final class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function contentRoutes(): array
    {
        return [
            'services' => ['admin.services.index'],
            'service create' => ['admin.services.create'],
            'insights' => ['admin.insights.index'],
            'insight create' => ['admin.insights.create'],
            'testimonials' => ['admin.testimonials.index'],
            'team' => ['admin.team.index'],
            'about overview' => ['admin.about.overview'],
            'about stats' => ['admin.about.stats'],
            'process' => ['admin.process.index'],
            'categories' => ['admin.categories.index'],
            'intake' => ['admin.intake.index'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function adminOnlyRoutes(): array
    {
        return [
            'settings' => ['admin.settings.edit'],
            'users' => ['admin.users.index'],
            'user create' => ['admin.users.create'],
        ];
    }

    #[DataProvider('contentRoutes')]
    public function test_an_editor_may_reach_every_content_screen(string $route): void
    {
        $this->actingAs(User::factory()->create())->get(route($route))->assertOk();
    }

    #[DataProvider('contentRoutes')]
    public function test_an_admin_may_reach_every_content_screen(string $route): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route($route))->assertOk();
    }

    #[DataProvider('adminOnlyRoutes')]
    public function test_an_editor_is_refused_configuration_and_accounts(string $route): void
    {
        $this->actingAs(User::factory()->create())->get(route($route))->assertForbidden();
    }

    #[DataProvider('adminOnlyRoutes')]
    public function test_an_admin_reaches_configuration_and_accounts(string $route): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route($route))->assertOk();
    }

    public function test_a_guest_is_redirected_to_the_sign_in_screen(): void
    {
        $this->get(route('admin.services.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_a_suspended_account_is_signed_out_on_its_next_request(): void
    {
        $this->actingAs(User::factory()->inactive()->create())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_the_navigation_only_offers_what_the_role_can_reach(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Insights')
            ->assertDontSee('Site settings');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Site settings');
    }

    public function test_an_editor_cannot_write_to_an_admin_only_module(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->put(route('admin.settings.update'), [])
            ->assertForbidden();

        $this->actingAs($editor)
            ->post(route('admin.users.store'), [])
            ->assertForbidden();
    }
}
