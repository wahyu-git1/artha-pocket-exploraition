<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\IncomeReceiptRequest;
use App\Http\Resources\V1\IncomeReceiptResource;
use App\Models\Income;
use App\Models\IncomeReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class IncomeReceiptController extends BaseController
{
    public function index(Income $income, Request $request): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $receipts = QueryBuilder::for(IncomeReceipt::class)
            ->where('income_id', $income->id)
            ->where('user_id', $request->user()->id)
            ->get();

        return $this->success(IncomeReceiptResource::collection($receipts));
    }

    public function store(IncomeReceiptRequest $request, Income $income): JsonResponse
    {
        if ($income->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $receipt = IncomeReceipt::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'client_id' => $request->client_id,
            ],
            [
                'income_id' => $income->id,
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'received_at' => $request->received_at,
                'note' => $request->note,
                'raw_input' => $request->raw_input,
            ]
        );

        return $this->created(new IncomeReceiptResource($receipt));
    }

    public function update(IncomeReceiptRequest $request, Income $income, IncomeReceipt $receipt): JsonResponse
    {
        if ($income->user_id !== $request->user()->id || $receipt->user_id !== $request->user()->id || $receipt->income_id !== $income->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $receipt->update($request->validated());

        return $this->success(new IncomeReceiptResource($receipt));
    }

    public function destroy(Income $income, IncomeReceipt $receipt, Request $request): JsonResponse
    {
        if ($income->user_id !== $request->user()->id || $receipt->user_id !== $request->user()->id || $receipt->income_id !== $income->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $receipt->delete();

        return $this->noContent();
    }
}
