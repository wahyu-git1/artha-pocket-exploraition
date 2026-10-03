<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\ExpenseRequest;
use App\Http\Resources\V1\ExpenseResource;
use App\Models\Expense;
use App\Models\Income;
use App\Services\IncomeBalanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExpenseController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $expenses = QueryBuilder::for(Expense::class)
            ->where('user_id', $request->user()->id)
            ->allowedFilters(...[
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('income_id'),
                AllowedFilter::callback('from', fn (Builder $query, $value) => $query->where('spent_at', '>=', $value)),
                AllowedFilter::callback('to', fn (Builder $query, $value) => $query->where('spent_at', '<=', $value)),
                AllowedFilter::callback('min_amount', fn (Builder $query, $value) => $query->where('amount', '>=', $value)),
                AllowedFilter::callback('max_amount', fn (Builder $query, $value) => $query->where('amount', '<=', $value)),
                AllowedFilter::callback('q', fn (Builder $query, $value) => $query->where('item', 'like', "%{$value}%")),
            ])
            ->allowedSorts('spent_at', 'amount')
            ->defaultSort('-spent_at')
            ->paginate($request->query('limit', 20));

        // Get total amount for the current query (ignoring pagination)
        $totalAmount = QueryBuilder::for(Expense::class)
            ->where('user_id', $request->user()->id)
            ->allowedFilters(...[
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('income_id'),
                AllowedFilter::callback('from', fn (Builder $query, $value) => $query->where('spent_at', '>=', $value)),
                AllowedFilter::callback('to', fn (Builder $query, $value) => $query->where('spent_at', '<=', $value)),
                AllowedFilter::callback('min_amount', fn (Builder $query, $value) => $query->where('amount', '>=', $value)),
                AllowedFilter::callback('max_amount', fn (Builder $query, $value) => $query->where('amount', '<=', $value)),
                AllowedFilter::callback('q', fn (Builder $query, $value) => $query->where('item', 'like', "%{$value}%")),
            ])
            ->sum('amount');

        $resource = ExpenseResource::collection($expenses);
        
        return response()->json([
            'data' => $resource,
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
                'total_amount' => (int) $totalAmount,
            ],
        ]);
    }

    public function store(ExpenseRequest $request, IncomeBalanceService $balanceService): JsonResponse
    {
        $user = $request->user();

        $expense = Expense::updateOrCreate(
            [
                'user_id' => $user->id,
                'client_id' => $request->client_id,
            ],
            [
                'income_id' => $request->income_id,
                'category_id' => $request->category_id,
                'item' => $request->item,
                'amount' => $request->amount,
                'spent_at' => $request->spent_at,
                'note' => $request->note,
                'raw_input' => $request->raw_input,
                'confidence_score' => $request->confidence_score,
                'source' => $request->source,
            ]
        );

        $income = Income::find($request->income_id);
        $newBalance = $balanceService->calculate($income);

        return response()->json([
            'data' => new ExpenseResource($expense),
            'meta' => [
                'income_balance' => $newBalance,
            ]
        ], $expense->wasRecentlyCreated ? 201 : 200);
    }

    public function bulk(ExpenseRequest $request, IncomeBalanceService $balanceService): JsonResponse
    {
        $user = $request->user();
        $expensesData = $request->validated('expenses');

        $createdExpenses = [];
        $affectedIncomeIds = [];

        DB::transaction(function () use ($expensesData, $user, &$createdExpenses, &$affectedIncomeIds) {
            foreach ($expensesData as $data) {
                $expense = Expense::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'client_id' => $data['client_id'],
                    ],
                    [
                        'income_id' => $data['income_id'],
                        'category_id' => $data['category_id'],
                        'item' => $data['item'],
                        'amount' => $data['amount'],
                        'spent_at' => $data['spent_at'],
                        'note' => $data['note'] ?? null,
                        'raw_input' => $data['raw_input'] ?? null,
                        'confidence_score' => $data['confidence_score'] ?? null,
                        'source' => $data['source'],
                    ]
                );
                
                $createdExpenses[] = $expense;
                $affectedIncomeIds[$data['income_id']] = true;
            }
        });

        $balances = [];
        foreach (array_keys($affectedIncomeIds) as $incomeId) {
            $balances[$incomeId] = $balanceService->calculate($incomeId);
        }

        return response()->json([
            'data' => ExpenseResource::collection($createdExpenses),
            'meta' => [
                'income_balances' => $balances,
            ]
        ], 201);
    }

    public function show(Expense $expense, Request $request): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        return $this->success(new ExpenseResource($expense));
    }

    public function update(ExpenseRequest $request, Expense $expense, IncomeBalanceService $balanceService): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $oldIncomeId = $expense->income_id;
        
        $expense->update($request->validated());

        $balances = [];
        $balances[$expense->income_id] = $balanceService->calculate($expense->income_id);
        
        if ($oldIncomeId !== $expense->income_id) {
            $balances[$oldIncomeId] = $balanceService->calculate($oldIncomeId);
        }

        return response()->json([
            'data' => new ExpenseResource($expense),
            'meta' => [
                'income_balances' => $balances,
            ]
        ]);
    }

    public function destroy(Expense $expense, Request $request, IncomeBalanceService $balanceService): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $incomeId = $expense->income_id;
        $expense->delete();

        return response()->json([
            'meta' => [
                'income_balance' => $balanceService->calculate($incomeId),
            ]
        ], 200); // Usually 204 no content, but since we return meta, we use 200
    }

    public function uploadReceipt(Request $request, Expense $expense): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('receipts', 'public');
        $expense->update(['receipt_image_path' => '/storage/' . $path]);

        return $this->success(new ExpenseResource($expense));
    }
}
