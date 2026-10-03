<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends BaseController
{
    /**
     * POST /api/v1/devices
     * Register or update user device push token.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_token' => 'required|string|max:255',
            'platform' => 'required|in:android,ios,web',
            'device_name' => 'required|string|max:100',
        ]);

        $device = Device::withTrashed()->updateOrCreate(
            ['push_token' => $validated['push_token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'],
                'last_seen_at' => now(),
                'deleted_at' => null, // restore if soft deleted previously
            ]
        );

        return $this->created([
            'id' => $device->id,
            'platform' => $device->platform,
            'device_name' => $device->device_name,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ]);
    }

    /**
     * DELETE /api/v1/devices/{device}
     * Revoke device token on logout.
     */
    public function destroy(Device $device, Request $request): JsonResponse
    {
        if ($device->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $device->delete();

        return $this->noContent();
    }
}
