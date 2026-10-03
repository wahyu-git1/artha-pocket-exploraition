<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeReceipt;
use Illuminate\Support\Facades\DB;

class SummaryService
{
    public function getMonthlySummary(string $userId, string $month): array
    {
        // $month format: YYYY-MM
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $incomes = Income::where('user_id', $userId)->get();
        
        $totalReceived = 0;
        $totalSpent = 0;
        $perIncome = [];

        foreach ($incomes as $income) {
            $received = IncomeReceipt::where('income_id', $income->id)
                            ->whereBetween('received_at', [$startDate, $endDate])
                            ->sum('amount');
            
            $spent = Expense::where('income_id', $income->id)
                            ->whereBetween('spent_at', [$startDate, $endDate])
                            ->sum('amount');
            
            $balance = (new IncomeBalanceService())->calculate($income);

            $totalReceived += $received;
            $totalSpent += $spent;

            $perIncome[] = [
                'income_id' => $income->id,
                'name' => $income->name,
                'received' => (int) $received,
                'spent' => (int) $spent,
                'balance' => $balance
            ];
        }

        $perCategoryRaw = Expense::where('expenses.user_id', $userId)
            ->whereBetween('expenses.spent_at', [$startDate, $endDate])
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.id', 'categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        $perCategory = [];
        foreach ($perCategoryRaw as $cat) {
            $perCategory[] = [
                'category_id' => $cat->id,
                'name' => $cat->name,
                'amount' => (int) $cat->total,
                'percentage' => $totalSpent > 0 ? round(($cat->total / $totalSpent) * 100, 2) : 0
            ];
        }

        return [
            'total_income' => (int) $totalReceived,
            'total_expense' => (int) $totalSpent,
            'balance' => (int) ($totalReceived - $totalSpent),
            'per_income' => $perIncome,
            'per_category' => $perCategory,
        ];
    }
}
