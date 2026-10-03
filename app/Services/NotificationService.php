<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create and record a user notification, then attempt push dispatch.
     */
    public function send(User $user, string $type, string $title, string $body, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $this->dispatchPushToDevices($user, $title, $body, array_merge(['type' => $type], $data));

        return $notification;
    }

    /**
     * Trigger Budget threshold alert (e.g. 80% or 100%).
     */
    public function triggerBudgetAlert(User $user, string $categoryName, int $percentage): Notification
    {
        $type = $percentage >= 100 ? 'budget_100' : 'budget_80';
        $title = $percentage >= 100 ? '⚠️ Anggaran Terlampaui!' : '⚡ Peringatan Batas Anggaran';
        $body = "Pengeluaran untuk {$categoryName} telah mencapai {$percentage}% dari alokasi bulanan.";

        return $this->send($user, $type, $title, $body, [
            'category_name' => $categoryName,
            'percentage' => $percentage,
            'route' => '/allocations',
        ]);
    }

    /**
     * Trigger Goal completed alert.
     */
    public function triggerGoalAchieved(User $user, string $goalName, int $targetPrice): Notification
    {
        $title = '🎉 Target Menabung Tercapai!';
        $body = "Selamat! Target \"{$goalName}\" sebesar Rp " . number_format($targetPrice, 0, ',', '.') . " berhasil Anda penuhi.";

        return $this->send($user, 'goal_done', $title, $body, [
            'goal_name' => $goalName,
            'target_price' => $targetPrice,
            'route' => '/savings-goals',
        ]);
    }

    /**
     * Trigger Emergency Fund transaction alert.
     */
    public function triggerEmergencyAlert(User $user, string $actionType, int $amount): Notification
    {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
        $title = $actionType === 'withdrawal' ? '🚨 Penarikan Dana Darurat' : '🛡️ Setoran Dana Darurat';
        $body = $actionType === 'withdrawal'
            ? "Tercatat penarikan dana darurat sebesar {$formatted}. Rencana pengisian kembali disarankan."
            : "Setoran dana darurat sebesar {$formatted} berhasil dicatat.";

        return $this->send($user, 'emergency_alert', $title, $body, [
            'action_type' => $actionType,
            'amount' => $amount,
            'route' => '/emergency-fund',
        ]);
    }

    /**
     * Dispatch FCM message to active devices.
     */
    protected function dispatchPushToDevices(User $user, string $title, string $body, array $payload): void
    {
        $devices = Device::where('user_id', $user->id)->pluck('push_token');

        if ($devices->isEmpty()) {
            return;
        }

        // Log push event for debugging and analytics
        Log::info("Push notification dispatched to user {$user->id} ({$devices->count()} devices)", [
            'title' => $title,
            'body' => $body,
            'tokens' => $devices->toArray(),
        ]);

        // If FCM server key is configured in env, send HTTP v1 / legacy FCM
        $fcmServerKey = config('services.fcm.server_key');
        if ($fcmServerKey) {
            try {
                Http::withHeaders([
                    'Authorization' => 'key=' . $fcmServerKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $devices->toArray(),
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                    ],
                    'data' => $payload,
                ]);
            } catch (\Throwable $e) {
                Log::warning('FCM dispatch failed: ' . $e->getMessage());
            }
        }
    }
}
