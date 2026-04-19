<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'currency',
        'symbol',
        'balance',
        'bonus_balance',
        'withdrawable_balance',
        'total_balance',
        'total_deposited',
        'total_withdrawn',
        'total_wagered',
        'rollover_remaining',
        'active',
    ];

    protected $casts = [
        'balance' => 'float',
        'bonus_balance' => 'float',
        'withdrawable_balance' => 'float',
        'total_balance' => 'float',
        'total_deposited' => 'float',
        'total_withdrawn' => 'float',
        'total_wagered' => 'float',
        'rollover_remaining' => 'float',
        'active' => 'boolean',
    ];

    public function getTotalBalanceAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $balance = (float) ($this->attributes['balance'] ?? 0);
        $bonus = (float) ($this->attributes['bonus_balance'] ?? 0);
        $withdrawable = (float) ($this->attributes['withdrawable_balance'] ?? 0);

        return $balance + $bonus + $withdrawable;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}