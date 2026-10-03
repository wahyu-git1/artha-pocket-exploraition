<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeReceipt extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'income_id',
        'user_id',
        'category_id',
        'amount',
        'received_at',
        'note',
        'raw_input',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'received_at' => 'date',
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
}
