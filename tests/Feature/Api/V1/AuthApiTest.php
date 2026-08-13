<?php

namespace Tests\Feature\Api\V1;

use App\Mail\BusinessRegistrationSuccessMail;
use App\Mail\EmailOtpMail;
use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthApiTest extends ApiTestCase
{
    public function test_register_creates_business_user_and_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Akhil',
            'email' => 'akhil@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Everything Easy Cafe',
            'business_type' => 'restaurant',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.business.name', 'Everything Easy Cafe');

        $this->assertDatabaseHas('businesses', ['name' => 'Everything Easy Cafe']);
        $this->assertDatabaseHas('users', ['email' => 'akhil@example.com', 'role' => 'owner']);
    }

    public function test_register_sends_success_email_when_smtp_is_enabled(): void
    {
        Mail::fake();
        $this->enableSmtpSettings();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mail Owner',
            'email' => 'mail-owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Mail Cafe',
            'business_type' => 'restaurant',
        ])->assertCreated()
            ->assertJsonPath('data.business.name', 'Mail Cafe');

        Mail::assertSent(BusinessRegistrationSuccessMail::class, function (BusinessRegistrationSuccessMail $mail) {
            return $mail->hasTo('mail-owner@example.com')
                && $mail->business->name === 'Mail Cafe';
        });
    }

    public function test_login_returns_token_and_me_requires_authentication(): void
    {
        [$business, $user] = $this->createBusinessUser('login@example.com');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonPath('data.business.id', $business->id);
        $token = $login->json('data.access_token');

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_can_check_email_availability_for_step_form(): void
    {
        $this->createBusinessUser('taken@example.com');

        $this->postJson('/api/v1/auth/check-email', [
            'email' => 'taken@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Email already exists')
            ->assertJsonPath('data.field', 'email')
            ->assertJsonPath('data.exists', true)
            ->assertJsonPath('data.available', false);

        $this->postJson('/api/v1/auth/check-email', [
            'email' => 'available@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Email is available')
            ->assertJsonPath('data.exists', false)
            ->assertJsonPath('data.available', true);
    }

    public function test_can_check_phone_availability_for_step_form(): void
    {
        [, $user] = $this->createBusinessUser('phone-check@example.com');
        $user->update(['phone' => '+919999999999']);

        $this->postJson('/api/v1/auth/check-phone', [
            'phone' => '+919999999999',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Phone number already exists')
            ->assertJsonPath('data.field', 'phone')
            ->assertJsonPath('data.exists', true)
            ->assertJsonPath('data.available', false);

        $this->postJson('/api/v1/auth/check-phone', [
            'phone' => '+918888888888',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Phone number is available')
            ->assertJsonPath('data.exists', false)
            ->assertJsonPath('data.available', true);
    }

    public function test_email_otp_requires_enabled_smtp_settings(): void
    {
        $this->postJson('/api/v1/auth/email-otp', [
            'email' => 'otp@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'SMTP settings are not enabled.');
    }

    public function test_email_otp_sends_mail_when_smtp_is_enabled(): void
    {
        Mail::fake();
        $this->enableSmtpSettings();

        $response = $this->postJson('/api/v1/auth/email-otp', [
            'email' => 'otp-mail@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Email OTP sent')
            ->assertJsonPath('data.email', 'otp-mail@example.com')
            ->assertJsonPath('data.expires_in_seconds', 300)
            ->assertJsonPath('data.mode', 'smtp')
            ->assertJsonPath('data.mail_sent', true);

        $this->assertArrayNotHasKey('otp', $response->json('data'));

        $sentOtp = null;

        Mail::assertSent(EmailOtpMail::class, function (EmailOtpMail $mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;

            return $mail->hasTo('otp-mail@example.com')
                && preg_match('/^\d{4}$/', $mail->otp) === 1;
        });

        $cachedOtp = Cache::get('auth:email-otp:'.sha1('otp-mail@example.com'));

        $this->assertIsArray($cachedOtp);
        $this->assertSame('otp-mail@example.com', $cachedOtp['email']);
        $this->assertTrue(Hash::check($sentOtp, $cachedOtp['otp_hash']));
    }

    public function test_email_otp_can_be_verified(): void
    {
        Mail::fake();
        $this->enableSmtpSettings();

        $this->postJson('/api/v1/auth/email-otp', [
            'email' => 'verify-otp@example.com',
        ])->assertOk();

        $sentOtp = null;

        Mail::assertSent(EmailOtpMail::class, function (EmailOtpMail $mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;

            return $mail->hasTo('verify-otp@example.com');
        });

        $this->postJson('/api/v1/auth/email-otp/verify', [
            'email' => 'verify-otp@example.com',
            'otp' => '0000',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid or expired OTP.');

        $this->postJson('/api/v1/auth/email-otp/verify', [
            'email' => 'verify-otp@example.com',
            'otp' => $sentOtp,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Email OTP verified')
            ->assertJsonPath('data.email', 'verify-otp@example.com')
            ->assertJsonPath('data.verified', true);

        $this->assertNull(Cache::get('auth:email-otp:'.sha1('verify-otp@example.com')));
    }

    public function test_auth_login_returns_token_for_active_staff(): void
    {
        [$business] = $this->createBusinessUser('staff-login-owner@example.com');
        $staff = User::create([
            'business_id' => $business->id,
            'name' => 'Rahul Waiter',
            'email' => 'staff-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff-login@example.com',
            'password' => 'password123',
            'device_name' => 'Waiter App',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged in successfully')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $staff->id)
            ->assertJsonPath('data.user.role', 'waiter')
            ->assertJsonPath('data.business.id', $business->id)
            ->assertJsonStructure(['data' => ['access_token']]);

        $token = $response->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $staff->id);
    }

    public function test_auth_login_rejects_inactive_staff(): void
    {
        [$business] = $this->createBusinessUser('inactive-staff-owner@example.com');
        User::create([
            'business_id' => $business->id,
            'name' => 'Inactive Waiter',
            'email' => 'inactive-staff-login@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'inactive',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive-staff-login@example.com',
            'password' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials');
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
