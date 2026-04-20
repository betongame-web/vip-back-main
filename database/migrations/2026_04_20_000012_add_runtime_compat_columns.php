<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            if (!Schema::hasColumn('settings', 'software_description')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->text('software_description')->nullable();
                });
            }

            foreach ([
                'software_logo_white' => null,
                'software_logo_black' => null,
                'decimal_format' => 'dot',
                'currency_position' => 'left',
                'prefix' => '$',
                'storage' => 'local',
            ] as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column, $default) {
                        $col = $table->string($column)->nullable();
                        if ($default !== null) {
                            $col->default($default);
                        }
                    });
                }
            }

            foreach ([
                'max_withdrawal' => 10000,
                'initial_bonus' => 0,
                'bonus_vip' => 0,
                'rollover_deposit' => 0,
                'withdrawal_limit' => 0,
                'withdrawal_period' => 0,
            ] as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column, $default) {
                        $table->decimal($column, 14, 2)->default($default);
                    });
                }
            }

            foreach ([
                'activate_vip_bonus' => false,
                'bspay_is_enable' => false,
                'turn_on_football' => false,
            ] as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column, $default) {
                        $table->boolean($column)->default($default);
                    });
                }
            }

            foreach ([
                'software_favicon', 'software_background',
                'ngr_percent', 'revshare_percentage', 'revshare_reverse',
                'soccer_percentage', 'perc_sub_lv1', 'perc_sub_lv2', 'perc_sub_lv3'
            ] as $column) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column) {
                        $table->string($column)->nullable();
                    });
                }
            }

            $settings = DB::table('settings')->first();
            if (!$settings) {
                DB::table('settings')->insert([
                    'software_name' => 'ViperPro',
                    'currency_code' => 'USD',
                    'currency_symbol' => '$',
                    'decimal_format' => 'dot',
                    'currency_position' => 'left',
                    'prefix' => '$',
                    'storage' => 'local',
                    'min_deposit' => 10,
                    'max_deposit' => 10000,
                    'min_withdrawal' => 20,
                    'max_withdrawal' => 10000,
                    'initial_bonus' => 0,
                    'bonus_vip' => 0,
                    'activate_vip_bonus' => false,
                    'suitpay_is_enable' => true,
                    'stripe_is_enable' => true,
                    'disable_spin' => false,
                    'rollover' => 1,
                    'language_default' => 'en',
                    'maintenance_mode' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::statement("UPDATE settings SET decimal_format = COALESCE(decimal_format, 'dot')");
                DB::statement("UPDATE settings SET currency_position = COALESCE(currency_position, 'left')");
                DB::statement("UPDATE settings SET prefix = COALESCE(prefix, '$')");
                DB::statement("UPDATE settings SET storage = COALESCE(storage, 'local')");
                DB::statement("UPDATE settings SET max_withdrawal = COALESCE(max_withdrawal, 10000)");
                DB::statement("UPDATE settings SET initial_bonus = COALESCE(initial_bonus, 0)");
                DB::statement("UPDATE settings SET bonus_vip = COALESCE(bonus_vip, 0)");
            }
        }

        if (Schema::hasTable('wallets')) {
            foreach ([
                'currency' => 'USD',
                'symbol' => '$',
            ] as $column => $default) {
                if (!Schema::hasColumn('wallets', $column)) {
                    Schema::table('wallets', function (Blueprint $table) use ($column, $default) {
                        $table->string($column)->nullable()->default($default);
                    });
                }
            }

            foreach ([
                'balance_bonus' => 0,
                'balance_withdrawal' => 0,
                'balance_bonus_rollover' => 0,
                'balance_deposit_rollover' => 0,
                'balance_demo' => 0,
                'refer_rewards' => 0,
            ] as $column => $default) {
                if (!Schema::hasColumn('wallets', $column)) {
                    Schema::table('wallets', function (Blueprint $table) use ($column, $default) {
                        $table->decimal($column, 14, 2)->default($default);
                    });
                }
            }

            if (!Schema::hasColumn('wallets', 'vip_points')) {
                Schema::table('wallets', function (Blueprint $table) {
                    $table->decimal('vip_points', 14, 2)->default(0);
                });
            }

            if (!Schema::hasColumn('wallets', 'vip_level')) {
                Schema::table('wallets', function (Blueprint $table) {
                    $table->integer('vip_level')->default(0);
                });
            }

            if (!Schema::hasColumn('wallets', 'status')) {
                Schema::table('wallets', function (Blueprint $table) {
                    $table->boolean('status')->default(true);
                });
            }

            if (Schema::hasColumn('wallets', 'bonus_balance') && Schema::hasColumn('wallets', 'balance_bonus')) {
                DB::statement("UPDATE wallets SET balance_bonus = COALESCE(NULLIF(balance_bonus, 0), bonus_balance, 0)");
            }
            if (Schema::hasColumn('wallets', 'withdrawable_balance') && Schema::hasColumn('wallets', 'balance_withdrawal')) {
                DB::statement("UPDATE wallets SET balance_withdrawal = COALESCE(NULLIF(balance_withdrawal, 0), withdrawable_balance, 0)");
            }
            if (Schema::hasColumn('wallets', 'active') && Schema::hasColumn('wallets', 'status')) {
                DB::statement("UPDATE wallets SET status = COALESCE(status, active)");
            }

            DB::statement("UPDATE wallets SET currency = COALESCE(currency, 'USD')");
            DB::statement("UPDATE wallets SET symbol = COALESCE(symbol, '$')");
            DB::statement("UPDATE wallets SET balance_bonus = COALESCE(balance_bonus, 0)");
            DB::statement("UPDATE wallets SET balance_withdrawal = COALESCE(balance_withdrawal, 0)");
            DB::statement("UPDATE wallets SET balance_bonus_rollover = COALESCE(balance_bonus_rollover, 0)");
            DB::statement("UPDATE wallets SET balance_deposit_rollover = COALESCE(balance_deposit_rollover, 0)");
            DB::statement("UPDATE wallets SET balance_demo = COALESCE(balance_demo, 0)");
            DB::statement("UPDATE wallets SET refer_rewards = COALESCE(refer_rewards, 0)");
            DB::statement("UPDATE wallets SET vip_points = COALESCE(vip_points, 0)");
            DB::statement("UPDATE wallets SET vip_level = COALESCE(vip_level, 0)");
            DB::statement("UPDATE wallets SET status = COALESCE(status, 1)");

            if (Schema::hasColumn('wallets', 'total_balance')) {
                DB::statement("UPDATE wallets SET total_balance = COALESCE(balance, 0) + COALESCE(balance_bonus, COALESCE(bonus_balance,0), 0) + COALESCE(balance_withdrawal, COALESCE(withdrawable_balance,0), 0)");
            }
        }
    }

    public function down(): void
    {
        // non-destructive rollback intentionally omitted
    }
};
