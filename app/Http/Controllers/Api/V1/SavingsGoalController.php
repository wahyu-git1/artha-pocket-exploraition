<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\SavingsGoalRequest;
use App\Http\Resources\V1\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SavingsGoalController extends BaseController
{
    public function simulate(Request $request, SavingsGoalService $service): JsonResponse
    {
        $request->validate([
            'price' => 'required|integer|min:1',
            'target_date' => 'required|date_format:Y-m-d|after:today',
            'saved_amount' => 'nullable|integer|min:0',
        ]);

        $result = $service->simulate(
            $request->user()->id, 
            $request->price, 
            $request->target_date, 
            $request->saved_amount ?? 0
        );

        return $this->success($result);
    }

    public function index(Request $request): JsonResponse
    {
        $goals = QueryBuilder::for(SavingsGoal::class)
            ->where('user_id', $request->user()->id)
            ->allowedFilters(['status'])
            ->get();

        return $this->success(SavingsGoalResource::collection($goals));
    }

    public function store(SavingsGoalRequest $request, SavingsGoalService $service): JsonResponse
    {
        $simulation = $service->simulate(
            $request->user()->id,
            $request->price,
            $request->target_date
        );

        $goal = SavingsGoal::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'price' => $request->price,
            'saved_amount' => 0,
            'target_date' => $request->target_date,
            'monthly_amount' => $simulation['monthly_amount'],
            'status' => 'active',
        ]);

        return response()->json([
            'data' => new SavingsGoalResource($goal),
            'meta' => [
                'warning' => $simulation['warning'],
                'alternative_dates' => $simulation['alternative_dates'],
            ]
        ], 201);
    }

    public function show(SavingsGoal $goal, Request $request): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        return $this->success(new SavingsGoalResource($goal));
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $goal, SavingsGoalService $service): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $goal->fill($request->validated());

        if ($goal->isDirty('price') || $goal->isDirty('target_date')) {
            $simulation = $service->simulate(
                $request->user()->id,
                $goal->price,
                $goal->target_date,
                $goal->saved_amount
            );
            $goal->monthly_amount = $simulation['monthly_amount'];
        }

        $goal->save();
        $service->checkCompletion($goal);

        return $this->success(new SavingsGoalResource($goal));
    }

    public function destroy(SavingsGoal $goal, Request $request): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        // When deleting, theoretically saved_amount is effectively "returned" to income balances.
        // It's mostly an audit / visual thing, as we don't have a specific income mapping here.
        // But we just soft delete it for now.
        $goal->delete();

        return $this->noContent();
    }
}
