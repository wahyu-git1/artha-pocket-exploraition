<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\InvestmentRequest;
use App\Http\Resources\V1\InvestmentResource;
use App\Models\Investment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class InvestmentController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $investments = QueryBuilder::for(Investment::class)
            ->where('user_id', $request->user()->id)
            ->allowedFilters('instrument_type', 'income_id')
            ->orderByDesc('invested_at')
            ->get();

        return $this->success(InvestmentResource::collection($investments));
    }

    public function store(InvestmentRequest $request): JsonResponse
    {
        $investment = DB::transaction(function () use ($request) {
            $inv = Investment::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'client_id' => $request->client_id,
                ],
                [
                    'income_id' => $request->income_id,
                    'instrument_type' => $request->instrument_type,
                    'instrument_name' => $request->instrument_name,
                    'amount' => $request->amount,
                    'invested_at' => $request->invested_at,
                    'note' => $request->note,
                ]
            );

            return $inv;
        });

        return $this->created(new InvestmentResource($investment));
    }

    public function show(Investment $investment, Request $request): JsonResponse
    {
        if ($investment->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        return $this->success(new InvestmentResource($investment));
    }

    public function update(InvestmentRequest $request, Investment $investment): JsonResponse
    {
        if ($investment->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $investment->update($request->validated());

        return $this->success(new InvestmentResource($investment));
    }

    public function destroy(Investment $investment, Request $request): JsonResponse
    {
        if ($investment->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $investment->delete();

        return $this->noContent();
    }
}
