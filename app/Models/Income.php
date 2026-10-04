<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'default_amount',
        'frequency',
        'pay_day',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'integer',
            'pay_day' => 'integer',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(IncomeReceipt::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function goalDeposits(): HasMany
    {
        return $this->hasMany(GoalDeposit::class);
    }

    public function emergencyTransactions(): HasMany
    {
        return $this->hasMany(EmergencyTransaction::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function allocationPlans(): HasMany
    {
        return $this->hasMany(AllocationPlan::class);
    }

    /**
     * Calculate current available balance for this income stream.
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalReceipts = (int) $this->receipts()->sum('amount');
                $totalExpenses = (int) $this->expenses()->sum('amount');
                $totalGoalDeposits = (int) $this->goalDeposits()->sum('amount');
                $totalEmergencyDeposits = (int) $this->emergencyTransactions()->where('type', 'deposit')->sum('amount');
                $totalEmergencyWithdrawals = (int) $this->emergencyTransactions()->where('type', 'withdrawal')->sum('amount');
                $totalInvestments = (int) $this->investments()->sum('amount');

                return $totalReceipts
                    - $totalExpenses
                    - $totalGoalDeposits
                    - $totalEmergencyDeposits
                    + $totalEmergencyWithdrawals
                    - $totalInvestments;
            }
        );
    }
}
