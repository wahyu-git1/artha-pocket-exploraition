<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return $this->success($notifications);
    }

    public function markRead(Notification $notif, Request $request): JsonResponse
    {
        if ($notif->user_id !== $request->user()->id) {
            return $this->error('FORBIDDEN', 'Akses ditolak', [], 403);
        }

        $notif->update(['read_at' => now()]);

        return $this->success($notif);
    }

    public function readAll(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(['message' => 'Semua notifikasi telah ditandai dibaca']);
    }
}
