<?php

namespace Tests\Feature\Api\V1;

use App\Models\Income;
use App\Models\SavingsGoal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SavingsGoalAndEmergencyTest extends TestCase
{
    protected User $user;
    protected Income $income;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Goal User ' . Str::random(5),
            'email' => strtolower('goal_' . Str::random(8) . '@example.com'),
            'password_hash' => Hash::make('password123'),
        ]);

        $this->income = Income::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Dompet Tabungan',
            'default_amount' => 5000000,
            'frequency' => 'monthly',
            'is_primary' => true,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_simulate_savings_goal(): void
    {
        $targetDate = Carbon::now()->addMonths(6)->toDateString();

        $response = $this->postJson('/api/v1/savings-goals/simulate', [
            'price' => 6000000,
            'target_date' => $targetDate,
            'saved_amount' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'price',
                    'target_date',
                    'months_left',
                    'monthly_amount',
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('data.months_left'));
    }

    public function test_can_deposit_to_savings_goal_and_auto_complete(): void
    {
        $goal = SavingsGoal::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->user->id,
            'name' => 'Beli Headphone',
            'price' => 500000,
            'saved_amount' => 0,
            'target_date' => Carbon::now()->addMonths(2)->toDateString(),
            'monthly_amount' => 250000,
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/v1/savings-goals/{$goal->id}/deposits", [
            'income_id' => $this->income->id,
            'amount' => 500000,
            'deposited_at' => Carbon::now()->toDateString(),
            'note' => 'Pelunasan',
            'client_id' => (string) Str::uuid(),
        ]);

        $response->assertStatus(201);

        $goal->refresh();
        $this->assertEquals(500000, $goal->saved_amount);
        $this->assertEquals('completed', $goal->status);
    }

    public function test_emergency_fund_recommendation(): void
    {
        $response = $this->postJson('/api/v1/emergency-fund/recommendation', [
            'marital_status' => 'married',
            'dependents_count' => 2,
            'income_stability' => 'variable',
            'has_installments' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'multiplier',
                    'reasoning',
                    'options',
                ],
            ]);

        // Base 3 + married 1 + dependents 2 + variable 2 + installments 1 = 9
        $this->assertEquals(9, $response->json('data.multiplier'));
    }
}
