<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\IncomeRequest;
use App\Http\Resources\V1\IncomeResource;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class IncomeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $incomes = QueryBuilder::for(Income::class)
            ->where('user_id', $request->user()->id)
            ->allowedFilters(['is_active'])
            ->get();

        return $this->success(IncomeResource::collection($incomes));
    }

    public function store(IncomeRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->is_primary) {
            Income::where('user_id', $user->id)->update(['is_primary' => false]);
        }

        $income = Income::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'default_amount' => $request->default_amount,
            'frequency' => $request->frequency,
            'pay_day' => $request->pay_day,
            'is_primary' => $request->is_primary ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return $this->created(new IncomeResource($income));
    }

    public function show(Income $income, Request $request): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        return $this->success(new IncomeResource($income));
    }

    public function update(IncomeRequest $request, Income $income): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        if ($request->has('is_primary') && $request->is_primary) {
            Income::where('user_id', $request->user()->id)
                ->where('id', '!=', $income->id)
                ->update(['is_primary' => false]);
        }

        $income->update($request->validated());

        return $this->success(new IncomeResource($income));
    }

    public function destroy(Income $income, Request $request): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $hasTransactions = $income->receipts()->exists() || $income->expenses()->exists();

        if ($hasTransactions) {
            return $this->error(
                ErrorCode::HAS_TRANSACTIONS,
                'Tidak dapat menghapus sumber pendapatan yang sudah memiliki transaksi.',
                [],
                409
            );
        }

        $income->delete();

        return $this->noContent();
    }
}
