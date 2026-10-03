<?php

namespace App\Services;

use App\Models\EmergencyFund;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;

class EmergencyFundService
{
    public function getRecommendation(User $user, array $overrides = []): array
    {
        $multiplier = 3; // Base
        $reasoning = ['Base recommendation: 3x monthly expense.'];

        $maritalStatus = $overrides['marital_status'] ?? $user->marital_status;
        $dependentsCount = $overrides['dependents_count'] ?? $user->dependents_count;
        $incomeStability = $overrides['income_stability'] ?? $user->income_stability;
        $hasInstallments = $overrides['has_installments'] ?? $user->has_installments;

        if ($maritalStatus === 'married') {
            $multiplier += 1;
            $reasoning[] = '+1x for married status.';
        }

        if ($dependentsCount > 0) {
            $added = min(3, (int) $dependentsCount);
            $multiplier += $added;
            $reasoning[] = "+{$added}x for dependents.";
        }

        if ($incomeStability === 'variable') {
            $multiplier += 2;
            $reasoning[] = '+2x for variable income.';
        }

        if ($hasInstallments) {
            $multiplier += 1;
            $reasoning[] = '+1x for having installments.';
        }

        $multiplier = min(12, $multiplier);

        $avgMonthlyExpense = $this->calculateAvgMonthlyExpense($user->id);
        $targetAmount = $avgMonthlyExpense * $multiplier;

        $options = [
            ['months' => 12, 'monthly_amount' => (int) ceil($targetAmount / 12)],
            ['months' => 24, 'monthly_amount' => (int) ceil($targetAmount / 24)],
            ['months' => 36, 'monthly_amount' => (int) ceil($targetAmount / 36)],
        ];

        return [
            'multiplier' => $multiplier,
            'avg_monthly_expense' => $avgMonthlyExpense,
            'target_amount' => $targetAmount,
            'reasoning' => $reasoning,
            'options' => $options,
        ];
    }

    protected function calculateAvgMonthlyExpense(string $userId): int
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
        $totalExpense = Expense::where('user_id', $userId)
            ->where('spent_at', '>=', $threeMonthsAgo)
            ->sum('amount');
        
        // Return average or a minimum default if 0
        $avg = (int) ceil($totalExpense / 3);
        return $avg > 0 ? $avg : 1000000; // default 1jt if no expense history
    }

    public function generateRefillPlan(EmergencyFund $fund): array
    {
        // Simple refill plan assuming we want to refill over the remaining plan_months or 6 months
        $shortfall = $fund->target_amount - $fund->saved_amount;
        $refillMonths = 6;
        $monthlyRefill = (int) ceil($shortfall / $refillMonths);

        return [
            'shortfall' => $shortfall,
            'suggested_refill_months' => $refillMonths,
            'suggested_monthly_refill' => $monthlyRefill,
        ];
    }
}
