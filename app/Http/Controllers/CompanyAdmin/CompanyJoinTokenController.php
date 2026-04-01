<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Services\CompanyJoinTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyJoinTokenController extends Controller
{
    public function __construct(private CompanyJoinTokenService $join_token_service) {}

    public function generateJoinToken(Request $request)
    {
        try {
            $request->validate([
                'label' => 'nullable|string|max:255',
                'expiry' => 'required|string',
                'usage_limit' => 'required|integer|min:1',
            ]);

            $user = Auth::user();
            $companyId = $user->company_id;

            $token = $this->join_token_service->generateToken(
                $companyId,
                $request->label,
                $request->expiry ?? '7d', // Default to 7 days
                $user->id,
                'user', // Created by a company admin (which is a 'user' role in this system)
                $request->usage_limit
            );

            return response()->json([
                'success' => true,
                'message' => 'Join token generated successfully!',
                'data' => [
                    'id' => $token->id,
                    'token' => $token->token,
                    'url' => env('WEB_APP_URL') . '/join?token=' . $token->token,
                    'label' => $token->label,
                    'expires_at' => $token->expires_at?->format('M d, Y H:i'),
                    'status' => $token->status,
                    'usage_limit' => $token->usage_limit,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function revokeJoinToken($tokenId)
    {
        try {
            $user = Auth::user();
            $token = \App\Models\CompanyJoinToken::where('id', $tokenId)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            $token->is_revoked = true;
            $token->save();

            return response()->json([
                'success' => true,
                'message' => 'Join token revoked successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getJoinTokens()
    {
        try {
            $user = Auth::user();
            $tokens = $this->join_token_service->getTokensForCompany($user->company_id);
            return response()->json([
                'success' => true,
                'data' => $tokens
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
