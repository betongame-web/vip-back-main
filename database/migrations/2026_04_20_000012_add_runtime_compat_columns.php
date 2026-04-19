<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SETTINGS TABLE COMPATIBILITY
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('settings')) {
            if (!Schema::hasColumn('settings', 'software_description')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->text('software_description')->nullable();
                });
            }

            $stringColumns = [
                'software_logo_white' => null,
                'software_logo_black' => null,
                'decimal_format' => 'dot',
                'currency_position' => 'left',
                'prefix' => '$',
                'storage' => 'local',
            ];

            foreach ($stringColumns as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column, $default) {
                        $col = $table->string($column)->nullable();
                        if ($default !== null) {
                            $col->default($default);
                        }
                    });
                }
            }

            $decimalColumns = [
                'max_withdrawal' => 10000,
                'initial_bonus' => 0,
                'bonus_vip' => 0,
            ];

            foreach ($decimalColumns as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column, $default) {
                        $table->decimal($column, 14, 2)->default($default);
                    });
                }
            }

            if (!Schema::hasColumn('settings', 'activate_vip_bonus')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->boolean('activate_vip_bonus')->default(false);
                });
            }

            $settings = DB::table('settings')->first();

            if (!$settings) {
                DB::table('settings')->insert([
                    'software_name' => 'ViperPro',
                    'software_description' => null,
                    'software_logo_white' => null,
                    'software_logo_black' => null,
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
                    'suitpay_is_enable' => true,
                    'stripe_is_enable' => true,
                    'disable_spin' => false,
                    'rollover' => 1,
                    'language_default' => 'en',
                    'maintenance_mode' => false,
                    'bonus_vip' => 0,
                    'activate_vip_bonus' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')->update([
                    'updated_at' => now(),
                ]);

                DB::statement("UPDATE settings SET decimal_format = COALESCE(decimal_format, 'dot')");
                DB::statement("UPDATE settings SET currency_position = COALESCE(currency_position, 'left')");
                DB::statement("UPDATE settings SET prefix = COALESCE(prefix, '$')");
                DB::statement("UPDATE settings SET storage = COALESCE(storage, 'local')");
                DB::statement("UPDATE settings SET max_withdrawal = COALESCE(max_withdrawal, 10000)");
                DB::statement("UPDATE settings SET initial_bonus = COALESCE(initial_bonus, 0)");
                DB::statement("UPDATE settings SET bonus_vip = COALESCE(bonus_vip, 0)");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | WALLETS TABLE COMPATIBILITY
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('wallets')) {
            $walletStringColumns = [
                'currency' => 'USD',
                'symbol' => '$',
            ];

            foreach ($walletStringColumns as $column => $default) {
                if (!Schema::hasColumn('wallets', $column)) {
                    Schema::table('wallets', function (Blueprint $table) use ($column, $default) {
                        $table->string($column)->nullable()->default($default);
                    });
                }
            }

            $walletDecimalColumns = [
                'balance_bonus' => 0,
                'balance_withdrawal' => 0,
                'balance_bonus_rollover' => 0,
                'balance_deposit_rollover' => 0,
                'balance_demo' => 0,
                'refer_rewards' => 0,
            ];

            foreach ($walletDecimalColumns as $column => $default) {
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
                DB::statement("
                    UPDATE wallets
                    SET balance_bonus = COALESCE(balance_bonus, 0) + COALESCE(bonus_balance, 0)
                    WHERE COALESCE(balance_bonus, 0) = 0
                ");
            }

            if (Schema::hasColumn('wallets', 'withdrawable_balance') && Schema::hasColumn('wallets', 'balance_withdrawal')) {
                DB::statement("
                    UPDATE wallets
                    SET balance_withdrawal = COALESCE(balance_withdrawal, 0) + COALESCE(withdrawable_balance, 0)
                    WHERE COALESCE(balance_withdrawal, 0) = 0
                ");
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

            if (Schema::hasColumn('wallets', 'updated_at')) {
                DB::table('wallets')->update([
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasColumn('wallets', 'total_balance')) {
                DB::statement("
                    UPDATE wallets
                    SET total_balance =
                        COALESCE(balance, 0) +
                        COALESCE(balance_bonus, 0) +
                        COALESCE(balance_withdrawal, 0)
                ");
            }
        }
    }

    public function down(): void
    {
        // Production data safe রাখার জন্য rollback empty রাখা হয়েছে
    }
};
