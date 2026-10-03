<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SmartEntryTest extends TestCase
{
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Smart Tester ' . Str::random(5),
            'email' => strtolower('smart_' . Str::random(8) . '@example.com'),
            'password_hash' => Hash::make('password123'),
        ]);

        $this->category = Category::where('type', 'expense')->first() ?? Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
            'bucket' => 'need',
            'icon' => 'fastfood',
            'color' => '#FF5722',
            'is_default' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_smart_entry_parse_success(): void
    {
        $response = $this->postJson('/api/v1/smart-entry/parse', [
            'text' => 'makan soto ayam 25k tadi siang',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['item', 'amount', 'type', 'spent_at', 'confidence_score'],
                    ],
                    'needs_review',
                ],
            ]);

        $this->assertEquals(25000, $response->json('data.items.0.amount'));
    }

    public function test_smart_entry_parse_without_amount_returns_422(): void
    {
        $response = $this->postJson('/api/v1/smart-entry/parse', [
            'text' => 'hanya teks tanpa nominal rupiah',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PARSE_NO_AMOUNT');
    }

    public function test_smart_entry_user_category_feedback(): void
    {
        $response = $this->postJson('/api/v1/smart-entry/feedback', [
            'keyword' => 'starbucks',
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.keyword', 'starbucks')
            ->assertJsonPath('data.category_id', $this->category->id);

        $this->assertDatabaseHas('user_category_preferences', [
            'user_id' => $this->user->id,
            'keyword' => 'starbucks',
            'category_id' => $this->category->id,
        ]);
    }
}
