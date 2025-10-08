<?php

namespace App\Models;

use App\Enums\SocialService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service',
        'service_user_id',
        'username',
        'access_token',
        'access_token_secret',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'is_active',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'service' => SocialService::class,
        'token_expires_at' => 'datetime',
        'scopes' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'access_token' => 'encrypted',
        'access_token_secret' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the token needs refresh (expires within 24 hours).
     */
    public function needsTokenRefresh(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isFuture() 
            && $this->token_expires_at->isBefore(now()->addDay());
    }

    /**
     * Get the display name for the account.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->username ?? $this->service_user_id ?? 'Unknown Account',
        );
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by service.
     */
    public function scopeForService($query, SocialService $service)
    {
        return $query->where('service', $service);
    }
}
