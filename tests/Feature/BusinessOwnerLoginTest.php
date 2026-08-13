<?php

namespace Tests\Feature;

use App\Models\MailSetting;
use App\Models\User;
use App\Services\MailBrandingService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class BusinessOwnerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_for_guests(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Business Owner Login')
            ->assertSee('Forgot password?');
    }

    public function test_business_owner_can_login_and_reach_dashboard(): void
    {
        User::create([
            'name' => 'Owner',
            'email' => 'owner-login@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'owner-login@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $this->get('/')->assertOk();
    }

    public function test_non_owner_staff_cannot_login_to_owner_dashboard(): void
    {
        User::create([
            'name' => 'Waiter',
            'email' => 'waiter-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'waiter-login@example.com',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_dashboard_request_redirects_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_active_portal_user_can_request_password_reset_link(): void
    {
        Notification::fake();
        $this->enableSmtpSettings();

        $user = User::create([
            'name' => 'Owner',
            'email' => 'reset-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset your password');

        $this->post(route('password.email'), [
            'email' => 'reset-owner@example.com',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Password reset link sent to your email.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_link_is_not_sent_for_missing_or_inactive_user(): void
    {
        Notification::fake();
        $this->enableSmtpSettings();

        User::create([
            'name' => 'Inactive Owner',
            'email' => 'inactive-reset@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'inactive',
        ]);

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'missing-reset@example.com',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'inactive-reset@example.com',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_active_portal_user_can_reset_password_from_reset_link(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'reset-link-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('Create new password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password updated. You can sign in now.');

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_password_reset_email_uses_product_branding(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'brand-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
            'profile_image_path' => 'profile-images/product-logo.png',
        ]);

        $user = User::create([
            'name' => 'Owner',
            'email' => 'brand-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        app(MailBrandingService::class)->apply();

        $html = (string) (new ResetPassword('reset-token'))->toMail($user)->render();

        $this->assertStringContainsString(asset('storage/profile-images/product-logo.png'), $html);
        $this->assertStringContainsString('EverythingEasy', $html);
        $this->assertStringNotContainsString('Laravel Logo', $html);
        $this->assertStringNotContainsString('laravel.com/img/notification-logo', $html);
    }

    private function enableSmtpSettings(): void
    {
        MailSetting::create([
            'enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'smtp-user',
            'password' => 'smtp-secret',
            'from_address' => 'noreply@example.com',
            'from_name' => 'EverythingEasy',
            'timeout' => 30,
        ]);
    }
}
