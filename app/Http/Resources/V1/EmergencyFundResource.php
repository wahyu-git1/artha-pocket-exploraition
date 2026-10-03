<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyFundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'multiplier' => $this->multiplier,
            'avg_monthly_expense' => $this->avg_monthly_expense,
            'target_amount' => $this->target_amount,
            'saved_amount' => $this->saved_amount,
            'plan_months' => $this->plan_months,
            'monthly_amount' => $this->monthly_amount,
            'status' => $this->status,
            'progress_percent' => $this->progress_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
