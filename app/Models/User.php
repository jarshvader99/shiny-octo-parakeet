<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Send the email verification notification (queued).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'zip_code',
        'congressional_district',
        'guidelines_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'zip_code_verified_at' => 'datetime',
            'guidelines_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has accepted community guidelines.
     */
    public function hasAcceptedGuidelines(): bool
    {
        return !is_null($this->guidelines_accepted_at);
    }

    /**
     * Accept community guidelines.
     */
    public function acceptGuidelines(): void
    {
        $this->update(['guidelines_accepted_at' => now()]);
    }

    /**
     * Determine if the user has completed their profile.
     */
    public function hasCompletedProfile(): bool
    {
        return !is_null($this->zip_code);
    }

    /**
     * Get all bills this user is following.
     */
    public function followedBills(): HasMany
    {
        return $this->hasMany(BillFollower::class);
    }

    /**
     * Check if user is following a specific bill.
     */
    public function isFollowing(Bill $bill): bool
    {
        return $this->followedBills()->where('bill_id', $bill->id)->exists();
    }

    /**
     * Follow a bill.
     */
    public function follow(Bill $bill, array $notificationPreferences = []): BillFollower
    {
        return $this->followedBills()->updateOrCreate(
            ['bill_id' => $bill->id],
            array_merge([
                'followed_at' => now(),
            ], $notificationPreferences)
        );
    }

    /**
     * Unfollow a bill.
     */
    public function unfollow(Bill $bill): bool
    {
        return $this->followedBills()->where('bill_id', $bill->id)->delete() > 0;
    }

    /**
     * Get all stances by this user.
     */
    public function stances(): HasMany
    {
        return $this->hasMany(UserStance::class);
    }

    /**
     * Get all comments by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
