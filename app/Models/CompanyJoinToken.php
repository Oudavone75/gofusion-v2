<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyJoinToken extends Model
{
    protected $fillable = [
        'company_id',
        'token',
        'label',
        'expires_at',
        'is_revoked',
        'created_by',
        'created_by_type',
        'usage_count',
        'usage_limit',
        'registration_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
        'usage_count' => 'integer',
        'usage_limit' => 'integer',
        'registration_count' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'join_token_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check token-level validity (expiry + revocation + usage limit).
     * Campaign-level check happens in CompanyJoinTokenService::resolveToken().
     */
    public function isValid(): bool
    {
        if ($this->is_revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get the display status of the token.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_revoked) {
            return 'revoked';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }
        return 'active';
    }
}
