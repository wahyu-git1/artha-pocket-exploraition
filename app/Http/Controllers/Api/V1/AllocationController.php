<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\AllocationCategoryMappingRequest;
use App\Http\Requests\Api\V1\AllocationRequest;
use App\Http\Resources\V1\AllocationPlanResource;
use App\Models\AllocationItem;
use App\Models\AllocationPlan;
use App\Models\CategoryBucketMapping;
use App\Services\AllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocationController extends BaseController
{
    public function templates(): JsonResponse
    {
        // Simple mock of templates (could be from DB seeder)
        $templates = [
            [
                'code' => '50-30-20',
                'name' => 'Standar (50/30/20)',
                'description' => '50% Kebutuhan, 30% Keinginan, 20% Tabungan/Investasi',
                'items' => [
                    ['bucket' => 'need', 'percent' => 50],
                    ['bucket' => 'want', 'percent' => 30],
                    ['bucket' => 'saving', 'percent' => 10],
                    ['bucket' => 'investment', 'percent' => 10],
                ]
            ],
            [
                'code' => '60-20-20',
                'name' => 'Agresif (60/20/20)',
                'description' => 'Fokus ke tabungan & bayar utang',
                'items' => [
                    ['bucket' => 'need', 'percent' => 60],
                    ['bucket' => 'want', 'percent' => 20],
                    ['bucket' => 'saving', 'percent' => 20],
                ]
            ]
        ];

        return $this->success($templates);
    }

    public function recommendation(Request $request, AllocationService $service): JsonResponse
    {
        $result = $service->getRecommendation($request->user()->id);
        return $this->success($result);
    }

    public function index(Request $request): JsonResponse
    {
        $month = $request->query('month', date('Y-m'));
        $plan = AllocationPlan::where('user_id', $request->user()->id)
                    ->where('month', $month)
                    ->with('items')
                    ->first();

        if (!$plan) {
            return $this->success(null);
        }

        return $this->success(new AllocationPlanResource($plan));
    }

    public function store(AllocationRequest $request, AllocationService $service): JsonResponse
    {
        if (!$service->validateTotalPercent($request->items)) {
            return $this->error(ErrorCode::VALIDATION_ERROR, 'Total persentase harus 100', [], 422);
        }

        $plan = DB::transaction(function () use ($request) {
            $plan = AllocationPlan::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'month' => $request->month,
                ],
                [
                    'income_id' => $request->income_id,
                    'template_code' => $request->template_code,
                ]
            );

            // Clear old items and recreate
            $plan->items()->delete();
            
            foreach ($request->items as $item) {
                AllocationItem::create([
                    'plan_id' => $plan->id,
                    'bucket' => $item['bucket'],
                    'percent' => $item['percent'],
                ]);
            }

            return $plan->load('items');
        });

        return $this->success(new AllocationPlanResource($plan));
    }

    public function summary(Request $request, AllocationService $service): JsonResponse
    {
        $month = $request->query('month', date('Y-m'));
        $summary = $service->getSummary($request->user()->id, $month);

        return $this->success($summary);
    }

    public function getCategoryMapping(Request $request): JsonResponse
    {
        $mappings = CategoryBucketMapping::where('user_id', $request->user()->id)->get();
        return $this->success($mappings);
    }

    public function updateCategoryMapping(AllocationCategoryMappingRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request) {
            foreach ($request->mappings as $mapping) {
                CategoryBucketMapping::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'category_id' => $mapping['category_id']
                    ],
                    [
                        'bucket' => $mapping['bucket']
                    ]
                );
            }
        });

        $mappings = CategoryBucketMapping::where('user_id', $request->user()->id)->get();
        return $this->success($mappings);
    }
}
