<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\ErrorCode;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RefreshTokenRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function register(RegisterRequest $request, TokenService $tokenService): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password_hash' => Hash::make($request->password),
        ]);

        $tokenData = $tokenService->createTokenPair($user);

        return $this->created([
            'user' => new UserResource($user),
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in' => $tokenData['expires_in'],
            'token_type' => $tokenData['token_type'],
        ]);
    }

    public function login(LoginRequest $request, TokenService $tokenService): JsonResponse
    {
        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return $this->error(
                ErrorCode::INVALID_CREDENTIALS,
                'Email atau kata sandi tidak valid.',
                [],
                401
            );
        }

        $tokenData = $tokenService->createTokenPair($user, $request->device_name);

        return $this->success([
            'user' => new UserResource($user),
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in' => $tokenData['expires_in'],
            'token_type' => $tokenData['token_type'],
        ]);
    }

    public function refresh(RefreshTokenRequest $request, TokenService $tokenService): JsonResponse
    {
        try {
            $result = $tokenService->rotateRefreshToken($request->refresh_token);

            return $this->success($result['tokens']);
        } catch (\RuntimeException $e) {
            $code = $e->getMessage() === ErrorCode::TOKEN_EXPIRED
                ? ErrorCode::TOKEN_EXPIRED
                : ErrorCode::TOKEN_INVALID;

            $message = $code === ErrorCode::TOKEN_EXPIRED
                ? 'Refresh token sudah kedaluwarsa.'
                : 'Refresh token tidak valid atau telah dicabut.';

            return $this->error($code, $message, [], 401);
        }
    }

    public function logout(Request $request, TokenService $tokenService): JsonResponse
    {
        if ($request->filled('refresh_token')) {
            $tokenService->revokeRefreshToken($request->refresh_token);
        }

        $request->user()?->currentAccessToken()?->delete();

        return $this->noContent();
    }
}
