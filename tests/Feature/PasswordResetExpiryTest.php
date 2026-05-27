<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Config::set('auth.passwords.users.expire', 10);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('mobile')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('role')->default('user');
        $table->string('provider')->default('email');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });
});

afterEach(function () {
    Carbon::setTestNow();
});

it('renders the password reset email with the configured 10 minute expiry', function () {
    $user = User::factory()->make(['email' => 'reset@example.com']);

    $html = (string) (new ResetPassword('sample-token'))->toMail($user)->render();

    expect($html)->toContain('This password reset link will expire in 10 minutes.');
    expect($html)->not->toContain('60 minutes');
});

it('allows a password reset before the 10 minute token expiry', function () {
    Carbon::setTestNow('2026-05-27 12:00:00');

    $user = User::factory()->create([
        'email' => 'within-window@example.com',
        'password' => Hash::make('OldPass123'),
    ]);
    $token = Password::broker()->createToken($user);

    Carbon::setTestNow('2026-05-27 12:09:59');

    $response = $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPass123',
        'password_confirmation' => 'NewPass123',
    ]);

    $response->assertRedirect(route('login'));
    expect(Hash::check('NewPass123', $user->fresh()->password))->toBeTrue();
});

it('rejects a password reset after the 10 minute token expiry', function () {
    Carbon::setTestNow('2026-05-27 12:00:00');

    $user = User::factory()->create([
        'email' => 'expired@example.com',
        'password' => Hash::make('OldPass123'),
    ]);
    $token = Password::broker()->createToken($user);

    Carbon::setTestNow('2026-05-27 12:10:01');

    $response = $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => $token, 'email' => $user->email]));
    $response->assertSessionHasErrors([
        'email' => 'This password reset link is invalid or has expired. Please request a new one.',
    ]);
    expect(Hash::check('OldPass123', $user->fresh()->password))->toBeTrue();
});
