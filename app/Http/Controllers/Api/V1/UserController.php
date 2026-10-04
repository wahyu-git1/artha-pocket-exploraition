<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\DeleteAccountRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());
        $user->save();

        return $this->success(new UserResource($user));
    }

    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->password, $user->password_hash)) {
            return $this->error(
                ErrorCode::WRONG_PASSWORD,
                'Kata sandi yang Anda masukkan salah.',
                [],
                403
            );
        }

        // Revoke active tokens
        $user->tokens()->delete();
        $user->refreshTokens()->update(['revoked_at' => now()]);

        // Soft delete user
        $user->delete();

        return $this->noContent();
    }
}
