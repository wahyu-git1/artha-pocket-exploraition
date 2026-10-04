<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeReceipt;
use App\Models\User;
use App\Services\IncomeBalanceService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IncomeAndBalanceTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Balance User ' . Str::random(5),
            'email' => strtolower('balance_' . Str::random(8) . '@example.com'),
            'password_hash' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_create_income_source(): void
    {
        $response = $this->postJson('/api/v1/incomes', [
            'name' => 'Gaji Kantor',
            'default_amount' => 7000000,
            'frequency' => 'monthly',
            'is_primary' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'default_amount', 'is_primary'],
            ]);

        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'name' => 'Gaji Kantor',
            'is_primary' => true,
        ]);
    }

    public function test_balance_calculation_formula(): void
    {
        $income = Income::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Rekening Tabungan',
            'default_amount' => 0,
            'frequency' => 'monthly',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 1. Add Receipt: +1.000.000
        IncomeReceipt::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $income->id,
            'amount' => 1000000,
            'received_at' => '2026-10-04',
            'client_id' => (string) Str::uuid(),
        ]);

        // 2. Add Expense: -250.000
        $category = Category::where('type', 'expense')->first();
        Expense::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $income->id,
            'category_id' => $category?->id,
            'item' => 'Belanja Mingguan',
            'amount' => 250000,
            'spent_at' => '2026-10-04',
            'source' => 'manual',
            'client_id' => (string) Str::uuid(),
        ]);

        /** @var IncomeBalanceService $balanceService */
        $balanceService = app(IncomeBalanceService::class);
        $calculatedBalance = $balanceService->calculate($income->id);

        $this->assertEquals(750000, $calculatedBalance);
    }

    public function test_cannot_delete_income_with_existing_transactions(): void
    {
        $income = Income::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Dompet Terpakai',
            'default_amount' => 0,
            'frequency' => 'monthly',
            'is_primary' => false,
            'is_active' => true,
        ]);

        IncomeReceipt::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'income_id' => $income->id,
            'amount' => 50000,
            'received_at' => '2026-10-04',
            'client_id' => (string) Str::uuid(),
        ]);

        $response = $this->deleteJson("/api/v1/incomes/{$income->id}");
        $response->assertStatus(409);
    }
}
