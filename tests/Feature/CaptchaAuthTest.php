<?php

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Config::set('services.turnstile.site_key', 'test-site-key');
    Config::set('services.turnstile.secret_key', 'test-secret-key');
});

test('user login is blocked when captcha verification fails', function () {
    $user = User::factory()->create([
        'email' => 'captcha-login@example.com',
        'password' => 'Password123',
        'role' => 'user',
    ]);

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'Password123',
        'role_scope' => 'user',
        'cf-turnstile-response' => 'bad-token',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertGuest();
});

test('user registration is blocked when captcha verification fails', function () {
    Mail::fake();
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Captcha User',
        'email' => 'captcha-register@example.com',
        'mobile' => '9876543210',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'cf-turnstile-response' => 'bad-token',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertDatabaseMissing('users', ['email' => 'captcha-register@example.com']);
    Mail::assertNotSent(OtpMail::class);
});

test('user registration succeeds when captcha verification passes', function () {
    Mail::fake();
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Valid Captcha User',
        'email' => 'valid-captcha@example.com',
        'mobile' => '9876543210',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'cf-turnstile-response' => 'valid-token',
    ]);

    $response->assertRedirect(route('otp.verify.form'));
    $this->assertDatabaseHas('users', ['email' => 'valid-captcha@example.com']);
    Mail::assertSent(OtpMail::class);
});
