<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
// FishCatch is the model for the catches table (Catch is a reserved PHP keyword)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar_path
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int $level
 * @property int $xp
 * @property string $location_privacy
 * @property string $unit_preference
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'avatar_path', 'password', 'level', 'xp', 'location_privacy', 'unit_preference'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Accessors appended to the model's array/JSON form so the frontend
     * always receives a ready-to-use avatar URL.
     *
     * @var list<string>
     */
    protected $appends = ['avatar'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'level' => 'integer',
            'xp' => 'integer',
        ];
    }

    /**
     * Public URL of the user's profile picture, or null when none is set.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
        );
    }

    public function catches(): HasMany
    {
        return $this->hasMany(FishCatch::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /** Species the user has caught at least once (Pokédex). */
    public function caughtSpecies(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'catches', 'user_id', 'species_id')
            ->distinct();
    }
}
