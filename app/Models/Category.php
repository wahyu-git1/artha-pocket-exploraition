<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'bucket',
        'icon',
        'color',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomeReceipts(): HasMany
    {
        return $this->hasMany(IncomeReceipt::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CategoryRule::class);
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserCategoryPreference::class);
    }

    public function bucketMappings(): HasMany
    {
        return $this->hasMany(CategoryBucketMapping::class);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true)->whereNull('user_id');
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_default', false)->whereNotNull('user_id');
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere(function (Builder $sub) {
                  $sub->where('is_default', true)->whereNull('user_id');
              });
        });
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }
}
