<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\GoalDepositRequest;
use App\Http\Resources\V1\GoalDepositResource;
use App\Models\GoalDeposit;
use App\Models\SavingsGoal;
use App\Services\IncomeBalanceService;
use App\Services\SavingsGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class GoalDepositController extends BaseController
{
    public function index(SavingsGoal $goal, Request $request): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $deposits = QueryBuilder::for(GoalDeposit::class)
            ->where('goal_id', $goal->id)
            ->orderByDesc('deposited_at')
            ->get();

        return $this->success(GoalDepositResource::collection($deposits));
    }

    public function store(GoalDepositRequest $request, SavingsGoal $goal, SavingsGoalService $service, IncomeBalanceService $balanceService): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        if ($goal->status === 'completed') {
            return $this->error(ErrorCode::GOAL_COMPLETED, 'Target sudah tercapai, tidak dapat menambah setoran.', [], 422);
        }

        $deposit = DB::transaction(function () use ($request, $goal, $service) {
            $deposit = GoalDeposit::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'client_id' => $request->client_id,
                ],
                [
                    'goal_id' => $goal->id,
                    'income_id' => $request->income_id,
                    'amount' => $request->amount,
                    'deposited_at' => $request->deposited_at,
                    'note' => $request->note,
                ]
            );

            // Re-calculate total saved amount because of updateOrCreate idempotency
            $goal->saved_amount = $goal->deposits()->sum('amount');
            $goal->save();
            
            $service->checkCompletion($goal);

            return $deposit;
        });

        return response()->json([
            'data' => new GoalDepositResource($deposit),
            'meta' => [
                'income_balance' => $balanceService->calculate($request->income_id),
                'goal_progress_percent' => $goal->progress_percent,
                'goal_status' => $goal->status,
            ]
        ], $deposit->wasRecentlyCreated ? 201 : 200);
    }
}
