<?php

namespace App\Services;

use App\Models\Income;

class IncomeBalanceService
{
    /**
     * Calculate current available balance for an income stream.
     *
     * @param string|Income $income
     * @return int
     */
    public function calculate(string|Income $income): int
    {
        if (is_string($income)) {
            $income = Income::findOrFail($income);
        }

        $totalReceipts = (int) $income->receipts()->sum('amount');
        $totalExpenses = (int) $income->expenses()->sum('amount');
        $totalGoalDeposits = (int) $income->goalDeposits()->sum('amount');
        $totalEmergencyDeposits = (int) $income->emergencyTransactions()->where('type', 'deposit')->sum('amount');
        $totalEmergencyWithdrawals = (int) $income->emergencyTransactions()->where('type', 'withdrawal')->sum('amount');
        $totalInvestments = (int) $income->investments()->sum('amount');

        return $totalReceipts
            - $totalExpenses
            - $totalGoalDeposits
            - $totalEmergencyDeposits
            + $totalEmergencyWithdrawals
            - $totalInvestments;
    }
}
