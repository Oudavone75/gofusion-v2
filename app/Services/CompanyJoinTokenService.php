<?php

namespace App\Services;

use App\Models\CompanyJoinToken;
use Carbon\Carbon;

class CompanyJoinTokenService
{
    /**
     * Generate a new join token for a company.
     */
    public function generateToken(int $companyId, ?string $label, ?string $expiry, $createdBy, string $createdByType = 'admin', ?int $usageLimit = null): CompanyJoinToken
    {
        $expiresAt = $this->resolveExpiry($expiry);

        return CompanyJoinToken::create([
            'company_id' => $companyId,
            'token' => bin2hex(random_bytes(32)),
            'label' => $label,
            'expires_at' => $expiresAt,
            'created_by' => $createdBy,
            'created_by_type' => $createdByType,
            'usage_limit' => $usageLimit,
        ]);
    }

    /**
     * Resolve a token: validate it and return company info if valid.
     * Checks 3 layers: token validity, company status, and usage limit.
     */
    /**
     * Resolve a token: validate it and return company info if valid.
     * Checks 3 layers: token validity, company status, and usage limit.
     */
    public function resolveToken(string $token, $user = null): array
    {
        $joinToken = CompanyJoinToken::with('company.mode', 'company.departments')->where('token', $token)->first();

        if (!$joinToken) {
            return ['success' => false, 'message' => trans('general.invalid_join_link')];
        }

        // Layer 1: Token-level check (expiry + revocation + usage limit)
        if (!$joinToken->isValid()) {
            $reason = 'expired';
            
            if ($joinToken->is_revoked) {
                $reason = 'revoked';
            } elseif ($joinToken->usage_limit !== null && $joinToken->usage_count >= $joinToken->usage_limit) {
                $reason = 'limit_reached';
            }
            
            return ['success' => false, 'message' => trans('general.join_link_' . $reason)];
        }

        // Layer 2: Company status check
        if ($joinToken->company->status !== 'active') {
            return ['success' => false, 'message' => trans('general.organization_not_found')];
        }

        // Increment usage count only if it's a first-time resolve for an unauthenticated user 
        // OR a different user who HAS NOT already joined this organization.
        $isAlreadyJoined = $user && $user->company_id == $joinToken->company_id;
        
        if (!$user || !$isAlreadyJoined) {
            $joinToken->increment('usage_count');
        }

        return [
            'success' => true,
            'message' => trans('general.organization_found'),
            'data' => $joinToken->company,
            'token_id' => $joinToken->id,
        ];
    }

    /**
     * Revoke a join token.
     */
    public function revokeToken(int $tokenId): bool
    {
        $token = CompanyJoinToken::findOrFail($tokenId);
        $token->is_revoked = true;
        return $token->save();
    }

    /**
     * Get all tokens for a company, ordered by most recent first.
     */
    public function getTokensForCompany(int $companyId): array
    {
        $tokens = CompanyJoinToken::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'token' => $token->token,
                    'label' => $token->label,
                    'url' => env('WEB_APP_URL') . '/join?token=' . $token->token,
                    'status' => $token->status,
                    'expires_at' => $token->expires_at?->format('M d, Y H:i'),
                    'usage_count' => $token->usage_count,
                    'usage_limit' => $token->usage_limit,
                    'registration_count' => $token->registration_count,
                    'created_at' => $token->created_at->format('M d, Y H:i'),
                ];
            });

        return [
            'active' => $tokens->where('status', 'active')->values()->toArray(),
            'inactive' => $tokens->whereIn('status', ['expired', 'revoked'])->values()->toArray(),
        ];
    }

    /**
     * Record a successful registration/conversion using a join token.
     * Returns the token ID if it counts as a new registration.
     */
    public function recordRegistration(string $tokenValue, $user = null): ?int
    {
        $joinToken = CompanyJoinToken::where('token', $tokenValue)->first();
        if (!$joinToken) return null;

        // Prevent double-counting if the user is already officially linked to this specific token
        if ($user && $user->join_token_id == $joinToken->id) {
            return $joinToken->id;
        }

        // Only increment if we haven't tracked this registration yet
        $joinToken->increment('registration_count');
        return $joinToken->id;
    }

    /**
     * Resolve expiry string to a Carbon datetime.
     * Supports presets (24h, 7d, etc.) or date strings (Y-m-d).
     */
    private function resolveExpiry(?string $expiry): ?Carbon
    {
        if (empty($expiry) || $expiry === 'never') {
            return null;
        }

        $preset = match ($expiry) {
            '24h' => Carbon::now()->addHours(24),
            '7d' => Carbon::now()->addDays(7),
            '30d' => Carbon::now()->addDays(30),
            '90d' => Carbon::now()->addDays(90),
            default => null,
        };

        if ($preset) return $preset;

        try {
            return Carbon::parse($expiry)->endOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
