<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SyncTest extends TestCase
{
    protected User $user;
    protected Income $income;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Sync Tester ' . Str::random(5),
            'email' => 'sync_' . Str::random(8) . '@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        $this->income = Income::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Dompet Offline',
            'default_amount' => 1000000,
            'frequency' => 'monthly',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->category = Category::where('type', 'expense')->first() ?? Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Sync',
            'type' => 'expense',
            'bucket' => 'need',
            'icon' => 'sync',
            'color' => '#2196F3',
            'is_default' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_sync_push_creates_offline_expense_successfully(): void
    {
        $clientId = (string) Str::uuid();

        $payload = [
            'client_time' => now()->toIso8601String(),
            'changes' => [
                [
                    'entity' => 'expense',
                    'op' => 'create',
                    'client_id' => $clientId,
                    'data' => [
                        'income_id' => $this->income->id,
                        'category_id' => $this->category->id,
                        'item' => 'Beli Bensin Offline',
                        'amount' => 50000,
                        'spent_at' => '2026-10-04',
                        'note' => 'Dicatat tanpa internet',
                        'source' => 'sync',
                    ],
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/sync/push', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.applied_count', 1)
            ->assertJsonPath('data.conflict_count', 0);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'client_id' => $clientId,
            'item' => 'Beli Bensin Offline',
            'amount' => 50000,
        ]);
    }

    public function test_sync_pull_returns_recent_changes_and_soft_deletes(): void
    {
        // 1. Create an active expense
        $expense = Expense::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Barang Aktif',
            'amount' => 45000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => (string) Str::uuid(),
        ]);

        // 2. Create and soft delete another expense
        $deletedExpense = Expense::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Barang Dihapus',
            'amount' => 10000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => (string) Str::uuid(),
        ]);
        $deletedExpense->delete();

        // 3. Pull since yesterday
        $since = urlencode(now()->subDays(1)->toIso8601String());
        $response = $this->getJson("/api/v1/sync/pull?since={$since}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'server_time',
                    'changes',
                ],
            ]);

        $changes = $response->json('data.changes');
        $this->assertNotEmpty($changes);

        // Verify that deleted expense appears as op: delete
        $hasDeletedOp = collect($changes)->contains(function ($item) use ($deletedExpense) {
            return $item['entity'] === 'expense'
                && $item['op'] === 'delete'
                && ($item['data']['id'] ?? '') === $deletedExpense->id;
        });

        $this->assertTrue($hasDeletedOp, 'Deleted item should have op: delete in pull response');
    }
}
