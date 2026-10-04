<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_register_successfully(): void
    {
        $uniqueEmail = 'register_' . Str::random(8) . '@example.com';

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Budi Santoso',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token',
                    'refresh_token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => strtolower($uniqueEmail)]);
    }

    public function test_duplicate_email_registration_fails(): void
    {
        $uniqueEmail = strtolower('dup_' . Str::random(8) . '@example.com');

        User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Existing User',
            'email' => $uniqueEmail,
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => ['code', 'message'],
            ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $uniqueEmail = strtolower('login_' . Str::random(8) . '@example.com');

        User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test User',
            'email' => $uniqueEmail,
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $uniqueEmail,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token',
                    'refresh_token',
                ],
            ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $uniqueEmail = 'wrong_' . Str::random(8) . '@example.com';

        User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test User',
            'email' => $uniqueEmail,
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $uniqueEmail,
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }
}
