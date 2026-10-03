<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends BaseController
{
    public function __construct(
        protected SyncService $syncService
    ) {}

    /**
     * POST /api/v1/sync/push
     * Accepts batch operations from mobile client when reconnecting online.
     */
    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_time' => 'nullable|string',
            'changes' => 'required|array',
            'changes.*.entity' => 'required|string',
            'changes.*.op' => 'required|in:create,update,delete,upsert',
            'changes.*.client_id' => 'required|string',
            'changes.*.data' => 'nullable|array',
            'changes.*.updated_at' => 'nullable|string',
        ]);

        $result = $this->syncService->push(
            $request->user(),
            $validated['changes'],
            $validated['client_time'] ?? null
        );

        return $this->success($result);
    }

    /**
     * GET /api/v1/sync/pull
     * Returns server-side modifications since client's last sync time.
     */
    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => 'nullable|string',
            'cursor' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $result = $this->syncService->pull(
            $request->user(),
            $validated['since'] ?? null,
            $validated['cursor'] ?? null,
            $validated['limit'] ?? 200
        );

        return $this->success($result);
    }
}
