<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'income_id' => $this->income_id,
            'instrument_type' => $this->instrument_type,
            'instrument_name' => $this->instrument_name,
            'amount' => $this->amount,
            'invested_at' => $this->invested_at,
            'note' => $this->note,
            'client_id' => $this->client_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
