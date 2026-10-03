<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    public function categories(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $from = $request->query('from', date('Y-m-01'));
        $to = $request->query('to', date('Y-m-t'));
        $type = $request->query('type', 'expense'); // For now, we only aggregate expenses

        $data = Expense::where('user_id', $userId)
            ->whereBetween('spent_at', [$from, $to])
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->where('categories.type', $type)
            ->select('categories.id', 'categories.name', 'categories.icon', 'categories.color', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->orderByDesc('total')
            ->get();

        return $this->success($data);
    }

    public function monthly(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $monthsToFetch = (int) $request->query('months', 6);
        
        $results = [];
        for ($i = $monthsToFetch - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $spent = Expense::where('user_id', $userId)
                        ->whereBetween('spent_at', [$startDate, $endDate])
                        ->sum('amount');
            
            $results[] = [
                'month' => $month,
                'total_expense' => (int) $spent,
            ];
        }

        return $this->success($results);
    }
}
