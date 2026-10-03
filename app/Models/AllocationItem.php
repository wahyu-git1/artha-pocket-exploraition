<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'bucket',
        'percent',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'float',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AllocationPlan::class, 'plan_id');
    }
}
