<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Editor',
            'email' => 'new.editor@formiva.test',
            'role' => UserRole::Editor->value,
            'password' => 'CorrectHorse12',
            'password_confirmation' => 'CorrectHorse12',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_an_admin_can_create_an_account(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.store'), $this->payload())
            ->assertRedirect();

        $user = User::query()->where('email', 'new.editor@formiva.test')->firstOrFail();

        $this->assertSame(UserRole::Editor, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('CorrectHorse12', $user->password));
    }

    public function test_a_weak_or_unconfirmed_password_is_refused(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->payload(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->payload(['password_confirmation' => 'Mismatched12']))
            ->assertSessionHasErrors('password');
    }

    public function test_email_uniqueness_holds_and_ignores_the_record_being_edited(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create(['email' => 'taken@formiva.test']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->payload(['email' => 'taken@formiva.test']))
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $existing), $this->payload([
                'email' => 'taken@formiva.test',
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_an_empty_password_on_edit_keeps_the_current_one(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['password' => 'OriginalPass12']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), $this->payload([
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect();

        $this->assertTrue(Hash::check('OriginalPass12', $user->fresh()->password));
    }

    public function test_an_admin_cannot_demote_or_suspend_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), $this->payload([
                'name' => 'Renamed',
                'email' => $admin->email,
                'role' => UserRole::Editor->value,
                'is_active' => '0',
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect();

        $admin->refresh();

        $this->assertSame('Renamed', $admin->name, 'Harmless edits still apply.');
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    public function test_the_last_active_admin_cannot_be_removed(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        // Suspending the second admin leaves exactly one active.
        $other->forceFill(['is_active' => false])->save();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $other))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $other->id]);

        $onlyAdmin = User::factory()->admin()->create();

        $this->actingAs($onlyAdmin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasNoErrors();
    }

    public function test_an_admin_can_delete_another_account_and_authored_entries_survive(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->create();
        $insight = \App\Models\Insight::factory()->create(['author_id' => $editor->id]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $editor))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $editor->id]);
        $this->assertDatabaseHas('insights', ['id' => $insight->id]);
    }

    public function test_the_list_filters_by_role_and_state(): void
    {
        // The signed-in name appears in the topbar on every screen, so the
        // absent-name assertions below use a different account.
        $admin = User::factory()->admin()->create(['name' => 'Signed In Admin']);
        User::factory()->admin()->create(['name' => 'Other Admin']);
        User::factory()->create(['name' => 'An Editor']);
        User::factory()->inactive()->create(['name' => 'Suspended Person']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => UserRole::Editor->value]))
            ->assertOk()
            ->assertSee('An Editor')
            ->assertDontSee('Other Admin');

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['state' => 'inactive']))
            ->assertOk()
            ->assertSee('Suspended Person')
            ->assertDontSee('An Editor');
    }

    public function test_an_editor_may_still_reach_their_own_profile(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get(route('admin.profile.edit'))->assertOk()->assertSee('Your profile');
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    }
}
