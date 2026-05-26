<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\PendingDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $this->rememberIntendedRedirect($request);

        return view('auth.login', ['activeTab' => 'login']);
    }

    public function showAdminLogin(Request $request)
    {
        $this->rememberIntendedRedirect($request);

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role_scope' => ['nullable', 'in:user,staff'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $key = 'login:'.Str::lower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if (! $user->email_verified_at) {
            RateLimiter::hit($key, 60);
            if (! $user->otp || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
                $this->sendOtp($user);
            }
            session(['otp_user_id' => $user->id]);

            return redirect()->route('otp.verify.form')->withErrors([
                'email' => 'Please verify your email before logging in.',
            ]);
        }

        if (($validated['role_scope'] ?? 'user') === 'staff' && ! $user->hasRole(['admin', 'team', 'sales', 'company', 'seo', 'dev', 'developer', 'article', 'article_writer'])) {
            throw ValidationException::withMessages(['email' => 'This login panel is for company and admin accounts.']);
        }

        if (($validated['role_scope'] ?? 'user') === 'user' && ! $user->hasRole('user')) {
            throw ValidationException::withMessages(['email' => 'Please use the company/admin login panel.']);
        }

        RateLimiter::clear($key);
        $remember = $request->boolean('remember');
        if ($remember && ! $user->getRememberToken()) {
            $user->setRememberToken(Str::random(60));
            $user->save();
        }

        $guestSessionId = $request->session()->getId();
        Auth::login($user, $remember);
        $request->session()->regenerate();
        app(PendingDownloadService::class)->attachPendingDocuments($request, $user, $guestSessionId);

        return redirect()->intended($this->postLoginRedirect($request, $user));
    }

    public function showRegister(Request $request)
    {
        $this->rememberIntendedRedirect($request);

        return view('auth.login', ['activeTab' => 'register']);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
            'role' => 'user',
            'provider' => 'email',
            'email_verified_at' => null,
        ]);

        $this->sendOtp($user);
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.verify.form')->with('status', 'We sent a 6-digit OTP to your email.');
    }

    public function showOtp()
    {
        abort_unless(session('otp_user_id'), 404);

        return view('auth.verify-otp', [
            'user' => User::findOrFail(session('otp_user_id')),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate(['otp' => ['required', 'digits:6']]);
        $user = User::findOrFail(session('otp_user_id'));

        if ($user->otp_attempts >= 5) {
            throw ValidationException::withMessages(['otp' => 'Maximum OTP attempts reached. Please resend a new OTP.']);
        }

        if (! $user->otp || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages(['otp' => 'This OTP has expired. Please request a new one.']);
        }

        if (! hash_equals($user->otp, $validated['otp'])) {
            $user->increment('otp_attempts');
            throw ValidationException::withMessages(['otp' => 'Invalid OTP.']);
        }

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->clearOtp();
        $request->session()->forget('otp_user_id');

        $guestSessionId = $request->session()->getId();
        Auth::login($user);
        $request->session()->regenerate();
        app(PendingDownloadService::class)->attachPendingDocuments($request, $user, $guestSessionId);

        return redirect()->intended($this->postLoginRedirect($request, $user));
    }

    public function resendOtp(Request $request)
    {
        $user = User::findOrFail(session('otp_user_id'));

        if ($user->otp_last_sent_at && $user->otp_last_sent_at->gt(now()->subSeconds(30))) {
            $seconds = 30 - $user->otp_last_sent_at->diffInSeconds(now());
            throw ValidationException::withMessages(['otp' => "Please wait {$seconds} seconds before resending OTP."]);
        }

        $this->sendOtp($user);

        return back()->with('status', 'A new OTP has been sent.');
    }

    public function showForgot()
    {
        return view('auth.login', ['activeTab' => 'forgot']);
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($validated);
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'email' => 'We could not send the reset email right now. Please check the mail settings and try again.',
            ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return back()->with('status', 'If an account exists, a reset link has been sent.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset($validated, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Your password has been reset.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ])->errorBag('updatePassword');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return back()->with('status', 'password-updated');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function sendOtp(User $user): void
    {
        $otp = $user->generateOtp();
        Mail::to($user->email)->send(new OtpMail($otp, $user->name));
    }

    private function redirectPath(User $user): string
    {
        return match ($user->role) {
            'admin', 'team', 'sales', 'seo', 'dev', 'developer', 'article', 'article_writer' => route('admin.dashboard'),
            'company' => route('company.dashboard'),
            default => route('dashboard'),
        };
    }

    private function postLoginRedirect(Request $request, User $user): string
    {
        if (app(PendingDownloadService::class)->hasPending($request) && $user->hasRole('user')) {
            $request->session()->forget('url.intended');
            return route('dashboard');
        }

        $intended = (string) $request->session()->get('url.intended', '');
        $plansUrl = route('plans');

        if ($user->activeSubscription?->hasDownloadsRemaining() && $intended) {
            $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '';
            $plansPath = parse_url($plansUrl, PHP_URL_PATH) ?: '/plans';

            if ($intendedPath === $plansPath) {
                $request->session()->forget('url.intended');
                return $this->redirectPath($user);
            }
        }

        if (! $intended) {
            return $this->redirectPath($user);
        }

        return $intended;
    }

    private function rememberIntendedRedirect(Request $request): void
    {
        $redirect = $request->query('redirect');

        if (! is_string($redirect) || $redirect === '') {
            return;
        }

        $appUrl = rtrim(url('/'), '/');
        $isLocalAbsolute = $redirect === $appUrl || Str::startsWith($redirect, $appUrl.'/');
        $isLocalRelative = Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//');

        if (! $isLocalAbsolute && ! $isLocalRelative) {
            return;
        }

        $request->session()->put('url.intended', $isLocalRelative ? url($redirect) : $redirect);
    }
}
