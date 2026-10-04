<?php

namespace App\Services;

use App\Models\Income;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SavingsGoalService
{
    public function simulate(string $userId, int $price, string $targetDate, int $savedAmount = 0): array
    {
        $monthsLeft = $this->calculateMonthsLeft($targetDate);
        $monthlyAmount = (int) ceil(($price - $savedAmount) / $monthsLeft);

        $totalBalance = $this->getTotalIncomeBalance($userId);

        $isRealistic = $monthlyAmount <= $totalBalance;
        $alternativeDates = [];

        if (!$isRealistic && $totalBalance > 0) {
            $realisticMonths = (int) ceil(($price - $savedAmount) / $totalBalance);
            $altDate = Carbon::now()->addMonths($realisticMonths)->format('Y-m-d');
            $alternativeDates[] = $altDate;
        }

        return [
            'price' => $price,
            'saved_amount' => $savedAmount,
            'target_date' => $targetDate,
            'months_left' => $monthsLeft,
            'monthly_amount' => $monthlyAmount,
            'is_realistic' => $isRealistic,
            'total_income_balance' => $totalBalance,
            'warning' => !$isRealistic ? 'Target tabungan bulanan melebihi total sisa pendapatan Anda.' : null,
            'alternative_dates' => $alternativeDates,
        ];
    }

    public function calculateMonthsLeft(string $targetDate): int
    {
        $target = Carbon::parse($targetDate)->startOfMonth();
        $now = Carbon::now()->startOfMonth();
        
        $diff = $target->diffInMonths($now);
        return max(1, (int) $diff); // Minimal 1 bulan
    }

    protected function getTotalIncomeBalance(string $userId): int
    {
        $incomes = Income::where('user_id', $userId)->get();
        $balanceService = new IncomeBalanceService();
        
        $total = 0;
        foreach ($incomes as $income) {
            $total += $balanceService->calculate($income);
        }

        // Pendapatan tidak tetap rata-rata 3 bulan (simplified for now, ideally we query avg receipts)
        // For simplicity, we just use the current balance of all incomes.
        return $total;
    }

    public function checkCompletion(SavingsGoal $goal): void
    {
        if ($goal->price - $goal->saved_amount <= 0 && $goal->status !== 'completed') {
            $goal->status = 'completed';
            $goal->completed_at = now();
            $goal->save();
            
            // Notification Trigger goes here later (Phase 2-4)
        }
    }
}
