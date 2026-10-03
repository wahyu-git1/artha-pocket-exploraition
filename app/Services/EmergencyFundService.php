<?php

namespace App\Services;

use App\Models\EmergencyFund;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;

class EmergencyFundService
{
    public function getRecommendation(User $user): array
    {
        $multiplier = 3; // Base
        $reasoning = ['Base recommendation: 3x monthly expense.'];

        if ($user->marital_status === 'married') {
            $multiplier += 1;
            $reasoning[] = '+1x for married status.';
        }

        if ($user->dependents_count > 0) {
            $added = min(3, $user->dependents_count);
            $multiplier += $added;
            $reasoning[] = "+{$added}x for dependents.";
        }

        if ($user->income_stability === 'variable') {
            $multiplier += 2;
            $reasoning[] = '+2x for variable income.';
        }

        if ($user->has_installments) {
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
