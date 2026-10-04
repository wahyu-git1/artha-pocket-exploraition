<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'income_id',
        'category_id',
        'item',
        'amount',
        'spent_at',
        'note',
        'raw_input',
        'confidence_score',
        'source',
        'client_id',
        'receipt_image_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'spent_at' => 'date',
            'confidence_score' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('spent_at', [$startDate, $endDate]);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeMonth(Builder $query, string $yearMonth): Builder
    {
        return $query->where('spent_at', 'like', "{$yearMonth}%");
    }
}
