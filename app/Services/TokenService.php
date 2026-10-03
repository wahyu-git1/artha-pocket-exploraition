<?php

namespace App\Services;

use App\Enums\ErrorCode;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TokenService
{
    public const ACCESS_TOKEN_EXPIRATION_SECONDS = 900; // 15 minutes
    public const REFRESH_TOKEN_EXPIRATION_DAYS = 30;

    /**
     * Issue an access token and a refresh token for a user.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    public function createTokenPair(User $user, ?string $deviceName = null): array
    {
        // 1. Access Token via Sanctum
        $tokenName = $deviceName ? "access_token_{$deviceName}" : 'access_token';
        $expiresAt = Carbon::now()->addSeconds(self::ACCESS_TOKEN_EXPIRATION_SECONDS);
        $accessToken = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        // 2. Refresh Token
        $plainRefreshToken = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'expires_at' => Carbon::now()->addDays(self::REFRESH_TOKEN_EXPIRATION_DAYS),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $plainRefreshToken,
            'expires_in' => self::ACCESS_TOKEN_EXPIRATION_SECONDS,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Verify and rotate a refresh token.
     *
     * @return array{user: User, tokens: array{access_token: string, refresh_token: string, expires_in: int, token_type: string}}
     * @throws \Exception
     */
    public function rotateRefreshToken(string $plainToken): array
    {
        $tokenHash = hash('sha256', $plainToken);

        $refreshToken = RefreshToken::where('token_hash', $tokenHash)->first();

        if (! $refreshToken || $refreshToken->revoked_at !== null) {
            throw new \RuntimeException(ErrorCode::TOKEN_INVALID);
        }

        if (Carbon::now()->greaterThan($refreshToken->expires_at)) {
            $refreshToken->update(['revoked_at' => Carbon::now()]);
            throw new \RuntimeException(ErrorCode::TOKEN_EXPIRED);
        }

        // Revoke the old refresh token (Token Rotation)
        $refreshToken->update(['revoked_at' => Carbon::now()]);

        $user = $refreshToken->user;
        if (! $user) {
            throw new \RuntimeException(ErrorCode::TOKEN_INVALID);
        }

        // Issue new token pair
        $newTokens = $this->createTokenPair($user);

        return [
            'user' => $user,
            'tokens' => $newTokens,
        ];
    }

    /**
     * Revoke a refresh token.
     */
    public function revokeRefreshToken(string $plainToken): bool
    {
        $tokenHash = hash('sha256', $plainToken);

        $token = RefreshToken::where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->first();

        if ($token) {
            $token->update(['revoked_at' => Carbon::now()]);
            return true;
        }

        return false;
    }
}
