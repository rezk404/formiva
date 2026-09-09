<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Insight;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_real_database_counts(): void
    {
        $user = User::factory()->admin()->create();
        Project::factory()->create();
        Client::factory()->create();
        Insight::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Projects')
            ->assertSee('Clients')
            ->assertSee('Insights');
    }

    public function test_active_editor_can_access_dashboard_but_not_user_management(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.dashboard'))->assertOk();
        $this->assertFalse(Gate::forUser($editor)->allows('viewAny', User::class));
    }

    public function test_admin_can_manage_users_and_editor_can_manage_content(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($editor)->allows('viewAny', Project::class));
        $this->assertTrue(Gate::forUser($editor)->allows('viewAny', Client::class));
    }

    public function test_profile_details_can_be_updated(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get(route('admin.profile.edit'))->assertOk()->assertSee('Your profile');
        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'Updated Staff',
            'email' => 'updated@formiva.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Staff', 'email' => 'updated@formiva.test']);
    }

    public function test_profile_rejects_duplicate_email(): void
    {
        $user = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $other->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_password_change_requires_current_password_and_succeeds(): void
    {
        $user = User::factory()->admin()->create(['password' => 'OldPassword123']);

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'wrong',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('admin.profile.password'), [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_public_routes_still_render(): void
    {
        foreach (['/', '/work', '/insights', '/start-a-project'] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
