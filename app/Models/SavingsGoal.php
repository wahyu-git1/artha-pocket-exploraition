<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsGoal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'saved_amount',
        'target_date',
        'monthly_amount',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'saved_amount' => 'integer',
            'monthly_amount' => 'integer',
            'target_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(GoalDeposit::class, 'goal_id');
    }

    protected function progressPercent(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->price <= 0) {
                    return 0;
                }
                return min(100.0, round(($this->saved_amount / $this->price) * 100, 1));
            }
        );
    }

    protected function monthsLeft(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->target_date) {
                    return 0;
                }
                $now = Carbon::today();
                $target = Carbon::parse($this->target_date);
                if ($now->greaterThanOrEqualTo($target)) {
                    return 0;
                }
                return (int) $now->diffInMonths($target);
            }
        );
    }
}
