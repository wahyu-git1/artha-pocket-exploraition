<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'marital_status',
        'dependents_count',
        'income_stability',
        'has_installments',
        'timezone',
        'primary_income_id',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'dependents_count' => 'integer',
            'has_installments' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function primaryIncome(): BelongsTo
    {
        return $this->belongsTo(Income::class, 'primary_income_id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function incomeReceipts(): HasMany
    {
        return $this->hasMany(IncomeReceipt::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function categoryPreferences(): HasMany
    {
        return $this->hasMany(UserCategoryPreference::class);
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function goalDeposits(): HasMany
    {
        return $this->hasMany(GoalDeposit::class);
    }

    public function emergencyFund(): HasOne
    {
        return $this->hasOne(EmergencyFund::class);
    }

    public function emergencyTransactions(): HasMany
    {
        return $this->hasMany(EmergencyTransaction::class);
    }

    public function allocationPlans(): HasMany
    {
        return $this->hasMany(AllocationPlan::class);
    }

    public function categoryBucketMappings(): HasMany
    {
        return $this->hasMany(CategoryBucketMapping::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
