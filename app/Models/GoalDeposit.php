<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalDeposit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'goal_id',
        'income_id',
        'user_id',
        'amount',
        'deposited_at',
        'note',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'deposited_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'goal_id');
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
