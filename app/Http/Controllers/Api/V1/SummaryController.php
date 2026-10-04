<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SummaryController extends BaseController
{
    public function index(Request $request, SummaryService $service): JsonResponse
    {
        $month = $request->query('month', date('Y-m'));
        
        $summary = $service->getMonthlySummary($request->user()->id, $month);

        return $this->success($summary);
    }
}
