<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyTransaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'fund_id',
        'income_id',
        'user_id',
        'type',
        'amount',
        'reason',
        'occurred_at',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'occurred_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(EmergencyFund::class, 'fund_id');
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
