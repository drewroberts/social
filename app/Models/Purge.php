<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purge extends Model
{
    /** @use HasFactory<\Database\Factories\PurgeFactory> */
    use HasFactory;

    protected $fillable = [
        'post_id',
        'posted_at',
        'text',
        'save',
        'account_id',
        'requested_at',
        'purged_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'requested_at' => 'datetime',
        'purged_at' => 'datetime',
        'save' => 'boolean',
    ];

    /**
     * Get the account that owns this purge.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope to get pending purges (not saved, not yet requested).
     */
    public function scopePending($query)
    {
        return $query->where('save', false)
                     ->whereNull('requested_at');
    }

    /**
     * Scope to get purged records.
     */
    public function scopePurged($query)
    {
        return $query->whereNotNull('purged_at');
    }

    /**
     * Scope to get saved/protected tweets.
     */
    public function scopeSaved($query)
    {
        return $query->where('save', true);
    }

    /**
     * Scope to get requested but not yet purged.
     */
    public function scopeRequested($query)
    {
        return $query->whereNotNull('requested_at')
                     ->whereNull('purged_at');
    }

    /**
     * Get the computed status of this purge.
     */
    public function getStatusAttribute(): string
    {
        if ($this->save) {
            return 'Saved';
        }

        if ($this->purged_at) {
            return 'Purged';
        }

        if ($this->requested_at) {
            return 'Requested';
        }

        return 'Pending';
    }
}
