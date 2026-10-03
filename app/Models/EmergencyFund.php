<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyFund extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'multiplier',
        'avg_monthly_expense',
        'target_amount',
        'saved_amount',
        'plan_months',
        'monthly_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'integer',
            'avg_monthly_expense' => 'integer',
            'target_amount' => 'integer',
            'saved_amount' => 'integer',
            'plan_months' => 'integer',
            'monthly_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EmergencyTransaction::class, 'fund_id');
    }

    protected function progressPercent(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->target_amount <= 0) {
                    return 0;
                }
                return min(100.0, round(($this->saved_amount / $this->target_amount) * 100, 1));
            }
        );
    }
}
