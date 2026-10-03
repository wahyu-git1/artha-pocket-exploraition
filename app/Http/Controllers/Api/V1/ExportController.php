<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends BaseController
{
    public function transactions(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->query('from', date('Y-m-01'));
        $to = $request->query('to', date('Y-m-t'));

        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('spent_at', [$from, $to])
            ->with(['category', 'income'])
            ->orderBy('spent_at')
            ->get();

        $csvFileName = "transactions_{$from}_to_{$to}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Item', 'Category', 'Amount', 'Income Source', 'Note'];

        $callback = function() use($expenses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($expenses as $expense) {
                $row = [
                    $expense->spent_at->format('Y-m-d'),
                    $expense->item,
                    $expense->category->name ?? '',
                    $expense->amount,
                    $expense->income->name ?? '',
                    $expense->note ?? ''
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
