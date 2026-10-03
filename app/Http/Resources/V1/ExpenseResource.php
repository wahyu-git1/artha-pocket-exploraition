<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'income_id' => $this->income_id,
            'category_id' => $this->category_id,
            'item' => $this->item,
            'amount' => $this->amount,
            'spent_at' => $this->spent_at,
            'note' => $this->note,
            'raw_input' => $this->raw_input,
            'confidence_score' => $this->confidence_score,
            'source' => $this->source,
            'client_id' => $this->client_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
