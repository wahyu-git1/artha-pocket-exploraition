<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'marital_status' => $this->marital_status,
            'dependents_count' => (int) $this->dependents_count,
            'income_stability' => $this->income_stability,
            'has_installments' => (bool) $this->has_installments,
            'timezone' => $this->timezone ?? 'Asia/Jakarta',
            'primary_income_id' => $this->primary_income_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
