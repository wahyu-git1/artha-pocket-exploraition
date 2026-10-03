<?php

namespace Tests\Feature\Api\V1;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Device User ' . Str::random(5),
            'email' => 'device_' . Str::random(8) . '@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_register_device_push_token(): void
    {
        $token = 'fcm_token_' . Str::random(20);

        $response = $this->postJson('/api/v1/devices', [
            'push_token' => $token,
            'platform' => 'android',
            'device_name' => 'Samsung S24 Ultra',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'platform', 'device_name'],
            ]);

        $this->assertDatabaseHas('devices', [
            'user_id' => $this->user->id,
            'push_token' => $token,
            'platform' => 'android',
        ]);
    }

    public function test_can_delete_device_token_on_logout(): void
    {
        $token = 'fcm_token_delete_' . Str::random(20);

        $device = Device::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'push_token' => $token,
            'platform' => 'ios',
            'device_name' => 'iPhone 15 Pro',
        ]);

        $response = $this->deleteJson("/api/v1/devices/{$device->id}");
        $response->assertStatus(204);

        $this->assertSoftDeleted('devices', [
            'id' => $device->id,
        ]);
    }
}
