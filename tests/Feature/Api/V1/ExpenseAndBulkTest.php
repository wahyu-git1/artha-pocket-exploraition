<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseAndBulkTest extends TestCase
{
    protected User $user;
    protected Income $income;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tester ' . Str::random(5),
            'email' => 'tester_' . Str::random(8) . '@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        $this->income = Income::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'BCA Utama',
            'default_amount' => 5000000,
            'frequency' => 'monthly',
            'is_primary' => true,
            'is_active' => true,
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

    public function test_user_can_create_expense_with_balance_update(): void
    {
        $clientId = (string) Str::uuid();

        $response = $this->postJson('/api/v1/expenses', [
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Nasi Padang',
            'amount' => 25000,
            'spent_at' => '2026-10-04',
            'note' => 'Makan siang',
            'source' => 'manual',
            'client_id' => $clientId,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'item', 'amount', 'spent_at'],
                'meta' => ['income_balance'],
            ]);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'client_id' => $clientId,
            'item' => 'Nasi Padang',
            'amount' => 25000,
        ]);
    }

    public function test_expense_creation_is_idempotent_with_client_id(): void
    {
        $clientId = (string) Str::uuid();

        $payload = [
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Kopi Latte',
            'amount' => 30000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => $clientId,
        ];

        $firstResponse = $this->postJson('/api/v1/expenses', $payload);
        $firstResponse->assertStatus(201);

        // Submit exact same client_id again
        $secondResponse = $this->postJson('/api/v1/expenses', $payload);
        $secondResponse->assertStatus(200);

        // Total count in database should remain 1
        $this->assertEquals(1, Expense::where('client_id', $clientId)->count());
    }

    public function test_bulk_expense_creation(): void
    {
        $payload = [
            'expenses' => [
                [
                    'income_id' => $this->income->id,
                    'category_id' => $this->category->id,
                    'item' => 'Item 1',
                    'amount' => 15000,
                    'spent_at' => '2026-10-04',
                    'source' => 'manual',
                    'client_id' => (string) Str::uuid(),
                ],
                [
                    'income_id' => $this->income->id,
                    'category_id' => $this->category->id,
                    'item' => 'Item 2',
                    'amount' => 20000,
                    'spent_at' => '2026-10-04',
                    'source' => 'manual',
                    'client_id' => (string) Str::uuid(),
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/expenses/bulk', $payload);
        $response->assertStatus(201)
            ->assertJsonCount(2, 'data');
    }

    public function test_cannot_delete_other_user_expense(): void
    {
        $otherUser = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other User',
            'email' => 'other_' . Str::random(8) . '@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        $otherExpense = Expense::create([
            'id' => (string) Str::uuid(),
            'user_id' => $otherUser->id,
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Barang Rahasia',
            'amount' => 999000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => (string) Str::uuid(),
        ]);

        // Acting as $this->user, try to delete other user's expense
        $response = $this->deleteJson("/api/v1/expenses/{$otherExpense->id}");
        $response->assertStatus(403);
    }

    public function test_upload_receipt_image(): void
    {
        Storage::fake('public');

        $expense = Expense::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $this->income->id,
            'category_id' => $this->category->id,
            'item' => 'Belanja Supermarket',
            'amount' => 150000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => (string) Str::uuid(),
        ]);

        $file = UploadedFile::fake()->image('struk.jpg', 600, 800);

        $response = $this->postJson("/api/v1/expenses/{$expense->id}/receipt", [
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'receipt_image_path'],
            ]);

        $expense->refresh();
        $this->assertNotNull($expense->receipt_image_path);
    }
}
