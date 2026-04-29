<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'otp',
        'otp_expires_at',
        'otp_attempts',
        'otp_last_sent_at',
        'google_id',
        'provider',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_last_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array(strtolower((string) $this->role), ['admin', 'super_admin', 'dev', 'developer', 'seo', 'article', 'article_writer'], true);
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return strtolower((string) $this->role) === 'user';
    }

    public function isCompany(): bool
    {
        return strtolower((string) $this->role) === 'company';
    }

    public function generateOtp(): string
    {
        $otp = (string) random_int(100000, 999999);

        $this->forceFill([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts' => 0,
            'otp_last_sent_at' => now(),
        ])->save();

        return $otp;
    }

    public function clearOtp(): void
    {
        $this->forceFill([
            'otp' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'otp_last_sent_at' => null,
        ])->save();
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->latestOfMany();
    }

    public function hasRole(string|array $roles): bool
    {
        $role = strtolower((string) $this->role);
        $allowed = array_map(fn ($item) => strtolower((string) $item), (array) $roles);

        return in_array($role, $allowed, true);
    }
}
