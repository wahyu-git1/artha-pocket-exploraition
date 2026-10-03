<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\EmergencyFundRequest;
use App\Http\Requests\Api\V1\EmergencyTransactionRequest;
use App\Http\Resources\V1\EmergencyFundResource;
use App\Http\Resources\V1\EmergencyTransactionResource;
use App\Models\EmergencyFund;
use App\Models\EmergencyTransaction;
use App\Services\EmergencyFundService;
use App\Services\IncomeBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class EmergencyFundController extends BaseController
{
    public function recommendation(Request $request, EmergencyFundService $service): JsonResponse
    {
        $result = $service->getRecommendation($request->user(), $request->all());
        return $this->success($result);
    }

    public function show(Request $request): JsonResponse
    {
        $fund = EmergencyFund::where('user_id', $request->user()->id)->first();
        
        if (!$fund) {
            return $this->error(ErrorCode::NOT_FOUND, 'Dana darurat belum di-setup.', [], 404);
        }

        return $this->success(new EmergencyFundResource($fund));
    }

    public function store(EmergencyFundRequest $request): JsonResponse
    {
        $existing = EmergencyFund::where('user_id', $request->user()->id)->exists();
        if ($existing) {
            return $this->error(ErrorCode::ALREADY_EXISTS, 'Dana darurat sudah ada.', [], 409);
        }

        $fund = EmergencyFund::create(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id, 'saved_amount' => 0, 'status' => 'active']
        ));

        return $this->created(new EmergencyFundResource($fund));
    }

    public function update(EmergencyFundRequest $request): JsonResponse
    {
        $fund = EmergencyFund::where('user_id', $request->user()->id)->firstOrFail();
        
        $fund->update($request->validated());

        return $this->success(new EmergencyFundResource($fund));
    }

    public function deposit(EmergencyTransactionRequest $request, IncomeBalanceService $balanceService): JsonResponse
    {
        $fund = EmergencyFund::where('user_id', $request->user()->id)->firstOrFail();

        $transaction = DB::transaction(function () use ($request, $fund) {
            $tx = EmergencyTransaction::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'client_id' => $request->client_id,
                ],
                [
                    'fund_id' => $fund->id,
                    'income_id' => $request->income_id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'occurred_at' => $request->occurred_at,
                    'reason' => $request->reason,
                ]
            );

            // Re-calculate saved_amount
            $deposits = EmergencyTransaction::where('fund_id', $fund->id)->where('type', 'deposit')->sum('amount');
            $withdrawals = EmergencyTransaction::where('fund_id', $fund->id)->where('type', 'withdrawal')->sum('amount');
            
            $fund->saved_amount = $deposits - $withdrawals;
            
            if ($fund->saved_amount >= $fund->target_amount) {
                $fund->status = 'completed';
            } else if ($fund->status === 'completed') {
                $fund->status = 'active'; // fallback if target increased
            }

            $fund->save();

            return $tx;
        });

        return response()->json([
            'data' => new EmergencyTransactionResource($transaction),
            'meta' => [
                'income_balance' => $balanceService->calculate($request->income_id),
                'fund_progress_percent' => $fund->progress_percent,
            ]
        ], $transaction->wasRecentlyCreated ? 201 : 200);
    }

    public function withdrawal(EmergencyTransactionRequest $request, EmergencyFundService $service): JsonResponse
    {
        $fund = EmergencyFund::where('user_id', $request->user()->id)->firstOrFail();

        if ($fund->saved_amount < $request->amount) {
            return $this->error(ErrorCode::INSUFFICIENT_FUND, 'Saldo dana darurat tidak cukup.', [], 400);
        }

        $transaction = DB::transaction(function () use ($request, $fund) {
            $tx = EmergencyTransaction::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'client_id' => $request->client_id,
                ],
                [
                    'fund_id' => $fund->id,
                    'income_id' => $request->income_id, // where the withdrawal goes (optional)
                    'type' => 'withdrawal',
                    'amount' => $request->amount,
                    'occurred_at' => $request->occurred_at,
                    'reason' => $request->reason,
                ]
            );

            $deposits = EmergencyTransaction::where('fund_id', $fund->id)->where('type', 'deposit')->sum('amount');
            $withdrawals = EmergencyTransaction::where('fund_id', $fund->id)->where('type', 'withdrawal')->sum('amount');
            
            $fund->saved_amount = $deposits - $withdrawals;
            $fund->status = 'active'; // Since it withdrew, no longer completed
            $fund->save();

            return $tx;
        });

        $refillPlan = $service->generateRefillPlan($fund);

        return response()->json([
            'data' => new EmergencyTransactionResource($transaction),
            'meta' => [
                'refill_plan' => $refillPlan,
                'fund_progress_percent' => $fund->progress_percent,
            ]
        ], $transaction->wasRecentlyCreated ? 201 : 200);
    }

    public function transactions(Request $request): JsonResponse
    {
        $fund = EmergencyFund::where('user_id', $request->user()->id)->firstOrFail();

        $transactions = QueryBuilder::for(EmergencyTransaction::class)
            ->where('fund_id', $fund->id)
            ->allowedFilters('type')
            ->orderByDesc('occurred_at')
            ->get();

        return $this->success(EmergencyTransactionResource::collection($transactions));
    }
}
