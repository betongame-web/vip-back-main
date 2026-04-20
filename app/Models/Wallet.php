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
        'balance_bonus',
        'balance_withdrawal',
        'balance_bonus_rollover',
        'balance_deposit_rollover',
        'balance_demo',
        'refer_rewards',
        'vip_points',
        'vip_level',
        'total_balance',
        'total_deposited',
        'total_withdrawn',
        'total_wagered',
        'rollover_remaining',
        'active',
        'status',
    ];

    protected $casts = [
        'balance' => 'float',
        'bonus_balance' => 'float',
        'withdrawable_balance' => 'float',
        'balance_bonus' => 'float',
        'balance_withdrawal' => 'float',
        'balance_bonus_rollover' => 'float',
        'balance_deposit_rollover' => 'float',
        'balance_demo' => 'float',
        'refer_rewards' => 'float',
        'vip_points' => 'float',
        'vip_level' => 'integer',
        'total_balance' => 'float',
        'total_deposited' => 'float',
        'total_withdrawn' => 'float',
        'total_wagered' => 'float',
        'rollover_remaining' => 'float',
        'active' => 'boolean',
        'status' => 'boolean',
    ];

    public function getCurrencyAttribute($value): string
    {
        return (string) ($value ?? 'USD');
    }

    public function getSymbolAttribute($value): string
    {
        return (string) ($value ?? '$');
    }

    public function getBalanceBonusAttribute($value): float
    {
        if ($value !== null) return (float) $value;
        return (float) ($this->attributes['bonus_balance'] ?? 0);
    }

    public function getBalanceWithdrawalAttribute($value): float
    {
        if ($value !== null) return (float) $value;
        return (float) ($this->attributes['withdrawable_balance'] ?? 0);
    }

    public function getBonusBalanceAttribute($value): float
    {
        if ($value !== null) return (float) $value;
        return (float) ($this->attributes['balance_bonus'] ?? 0);
    }

    public function getWithdrawableBalanceAttribute($value): float
    {
        if ($value !== null) return (float) $value;
        return (float) ($this->attributes['balance_withdrawal'] ?? 0);
    }

    public function getStatusAttribute($value): bool
    {
        if ($value !== null) return (bool) $value;
        return (bool) ($this->attributes['active'] ?? true);
    }

    public function getActiveAttribute($value): bool
    {
        if ($value !== null) return (bool) $value;
        return (bool) ($this->attributes['status'] ?? true);
    }

    public function getTotalBalanceAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $balance = (float) ($this->attributes['balance'] ?? 0);
        $bonus = (float) ($this->attributes['balance_bonus'] ?? ($this->attributes['bonus_balance'] ?? 0));
        $withdrawable = (float) ($this->attributes['balance_withdrawal'] ?? ($this->attributes['withdrawable_balance'] ?? 0));

        return $balance + $bonus + $withdrawable;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
