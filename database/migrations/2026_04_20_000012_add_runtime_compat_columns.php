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

            $settingsColumns = Schema::getColumnListing('settings');
            $settings = DB::table('settings')->first();

            if (!$settings) {
                $defaultSettings = [
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
                ];

                $insertData = array_intersect_key($defaultSettings, array_flip($settingsColumns));

                if (!empty($insertData)) {
                    DB::table('settings')->insert($insertData);
                }
            } else {
                if (in_array('updated_at', $settingsColumns, true)) {
                    DB::table('settings')->update([
                        'updated_at' => now(),
                    ]);
                }

                if (in_array('decimal_format', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET decimal_format = COALESCE(decimal_format, 'dot')");
                }

                if (in_array('currency_position', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET currency_position = COALESCE(currency_position, 'left')");
                }

                if (in_array('prefix', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET prefix = COALESCE(prefix, '$')");
                }

                if (in_array('storage', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET storage = COALESCE(storage, 'local')");
                }

                if (in_array('max_withdrawal', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET max_withdrawal = COALESCE(max_withdrawal, 10000)");
                }

                if (in_array('initial_bonus', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET initial_bonus = COALESCE(initial_bonus, 0)");
                }

                if (in_array('bonus_vip', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET bonus_vip = COALESCE(bonus_vip, 0)");
                }

                if (in_array('activate_vip_bonus', $settingsColumns, true)) {
                    DB::statement("UPDATE settings SET activate_vip_bonus = CASE WHEN activate_vip_bonus IS NULL THEN FALSE ELSE activate_vip_bonus END");
                }
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

            $walletColumns = Schema::getColumnListing('wallets');

            if (in_array('bonus_balance', $walletColumns, true) && in_array('balance_bonus', $walletColumns, true)) {
                DB::statement("
                    UPDATE wallets
                    SET balance_bonus = COALESCE(balance_bonus, 0) + COALESCE(bonus_balance, 0)
                    WHERE COALESCE(balance_bonus, 0) = 0
                ");
            }

            if (in_array('withdrawable_balance', $walletColumns, true) && in_array('balance_withdrawal', $walletColumns, true)) {
                DB::statement("
                    UPDATE wallets
                    SET balance_withdrawal = COALESCE(balance_withdrawal, 0) + COALESCE(withdrawable_balance, 0)
                    WHERE COALESCE(balance_withdrawal, 0) = 0
                ");
            }

            if (in_array('currency', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET currency = COALESCE(currency, 'USD')");
            }

            if (in_array('symbol', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET symbol = COALESCE(symbol, '$')");
            }

            if (in_array('balance_bonus', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET balance_bonus = COALESCE(balance_bonus, 0)");
            }

            if (in_array('balance_withdrawal', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET balance_withdrawal = COALESCE(balance_withdrawal, 0)");
            }

            if (in_array('balance_bonus_rollover', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET balance_bonus_rollover = COALESCE(balance_bonus_rollover, 0)");
            }

            if (in_array('balance_deposit_rollover', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET balance_deposit_rollover = COALESCE(balance_deposit_rollover, 0)");
            }

            if (in_array('balance_demo', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET balance_demo = COALESCE(balance_demo, 0)");
            }

            if (in_array('refer_rewards', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET refer_rewards = COALESCE(refer_rewards, 0)");
            }

            if (in_array('vip_points', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET vip_points = COALESCE(vip_points, 0)");
            }

            if (in_array('vip_level', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET vip_level = COALESCE(vip_level, 0)");
            }

            if (in_array('status', $walletColumns, true)) {
                DB::statement("UPDATE wallets SET status = CASE WHEN status IS NULL THEN TRUE ELSE status END");
            }

            if (in_array('updated_at', $walletColumns, true)) {
                DB::table('wallets')->update([
                    'updated_at' => now(),
                ]);
            }

            if (in_array('total_balance', $walletColumns, true)) {
                $balanceExpr = in_array('balance', $walletColumns, true) ? 'COALESCE(balance, 0)' : '0';
                $bonusExpr = in_array('balance_bonus', $walletColumns, true)
                    ? 'COALESCE(balance_bonus, 0)'
                    : (in_array('bonus_balance', $walletColumns, true) ? 'COALESCE(bonus_balance, 0)' : '0');
                $withdrawExpr = in_array('balance_withdrawal', $walletColumns, true)
                    ? 'COALESCE(balance_withdrawal, 0)'
                    : (in_array('withdrawable_balance', $walletColumns, true) ? 'COALESCE(withdrawable_balance, 0)' : '0');

                DB::statement("
                    UPDATE wallets
                    SET total_balance = {$balanceExpr} + {$bonusExpr} + {$withdrawExpr}
                ");
            }
        }
    }

    public function down(): void
    {
        // production safe rollback intentionally left empty
    }
};