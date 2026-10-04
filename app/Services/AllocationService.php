<?php

namespace App\Services;

use App\Models\AllocationPlan;
use App\Models\CategoryBucketMapping;
use App\Models\Expense;
use App\Models\GoalDeposit;
use App\Models\EmergencyTransaction;
use App\Models\Investment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AllocationService
{
    public function getRecommendation(string $userId): array
    {
        // Simple recommendation based on standard 50/30/20 if no history
        // Ideally we check 3 months history. Let's do a basic one.
        
        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
        
        // Let's assume standard if no history
        return [
            'template' => '50-30-20',
            'items' => [
                ['bucket' => 'need', 'percent' => 50],
                ['bucket' => 'want', 'percent' => 30],
                ['bucket' => 'saving', 'percent' => 10], // Split saving and investment
                ['bucket' => 'investment', 'percent' => 10],
            ],
            'reasoning' => 'Berdasarkan standar alokasi ideal 50-30-20.',
        ];
    }

    public function getSummary(string $userId, string $month): array
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $plan = AllocationPlan::where('user_id', $userId)->where('month', $month)->with('items')->first();

        // Get actual spend per bucket
        $expenses = Expense::where('expenses.user_id', $userId)
            ->whereBetween('spent_at', [$startDate, $endDate])
            ->leftJoin('category_bucket_mappings', function($join) use ($userId) {
                $join->on('expenses.category_id', '=', 'category_bucket_mappings.category_id')
                     ->where('category_bucket_mappings.user_id', '=', $userId);
            })
            ->select('category_bucket_mappings.bucket', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('category_bucket_mappings.bucket')
            ->get();

        $realized = [
            'need' => 0,
            'want' => 0,
            'saving' => 0,
            'investment' => 0,
            'unmapped' => 0,
        ];

        foreach ($expenses as $exp) {
            $bucket = $exp->bucket ?? 'unmapped';
            if (isset($realized[$bucket])) {
                $realized[$bucket] += $exp->total;
            } else {
                $realized['unmapped'] += $exp->total;
            }
        }

        // Add savings deposits
        $savingDeposits = GoalDeposit::where('user_id', $userId)
            ->whereBetween('deposited_at', [$startDate, $endDate])
            ->sum('amount');
        
        $emergencyDeposits = EmergencyTransaction::where('user_id', $userId)
            ->where('type', 'deposit')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->sum('amount');
        
        $realized['saving'] += $savingDeposits + $emergencyDeposits;

        // Add investments
        $investments = Investment::where('user_id', $userId)
            ->whereBetween('invested_at', [$startDate, $endDate])
            ->sum('amount');
        
        $realized['investment'] += $investments;

        return [
            'plan' => $plan ? $plan->items : [],
            'realized' => $realized,
        ];
    }

    public function validateTotalPercent(array $items): bool
    {
        $total = 0;
        foreach ($items as $item) {
            $total += (float) $item['percent'];
        }

        return abs($total - 100.0) < 0.01; // floating point comparison
    }
}
