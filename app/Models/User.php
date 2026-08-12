<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id',
        'name',
        'email',
        'phone',
        'expo_push_token',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'expo_push_token',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_user_id');
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class);
    }

    public function createApiToken(string $name = 'mobile', ?\DateTimeInterface $expiresAt = null): string
    {
        $plainTextToken = Str::random(80);

        $this->accessTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
        ]);

        return $plainTextToken;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
