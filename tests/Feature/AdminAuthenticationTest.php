<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Sign in');
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_public_routes_do_not_depend_on_admin_authentication(): void
    {
        foreach (['/', '/work', '/insights', '/start-a-project'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_valid_credentials_authenticate_and_regenerate_session(): void
    {
        $user = User::factory()->admin()->create(['password' => 'password']);
        $before = session()->getId();

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->admin()->create(['password' => 'password']);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_login_or_access_admin(): void
    {
        $user = User::factory()->admin()->inactive()->create(['password' => 'password']);

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_invalidates_the_session(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_password_reset_request_is_generic_and_notifies_matching_user(): void
    {
        Notification::fake();
        $user = User::factory()->admin()->create();

        $this->post(route('admin.forgot-password.store'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_completes_and_authenticates_user(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password']);
        /** @var PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken($user);

        $this->post(route('admin.reset-password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        $this->post(route('admin.reset-password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->admin()->create(['password' => 'password']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertStatus(429);
    }
}
