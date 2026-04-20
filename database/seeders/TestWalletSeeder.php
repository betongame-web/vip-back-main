<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestWalletSeeder extends Seeder
{
    public function run(): void
    {
        $user = DB::table('users')->where('email', 'test@viperpro.com')->first();

        if (!$user) {
            return;
        }

        $existing = DB::table('wallets')->where('user_id', $user->id)->first();

        $data = [];
        $set = function (string $column, $value) use (&$data) {
            if (Schema::hasColumn('wallets', $column)) {
                $data[$column] = $value;
            }
        };

        $set('user_id', $user->id);
        $set('currency', 'USD');
        $set('symbol', '$');
        $set('balance', 1000);
        $set('bonus_balance', 0);
        $set('withdrawable_balance', 0);
        $set('balance_bonus', 0);
        $set('balance_withdrawal', 0);
        $set('balance_bonus_rollover', 0);
        $set('balance_deposit_rollover', 0);
        $set('balance_demo', 1000);
        $set('refer_rewards', 0);
        $set('vip_points', 0);
        $set('vip_level', 0);
        $set('total_balance', 1000);
        $set('total_deposited', 1000);
        $set('total_withdrawn', 0);
        $set('total_wagered', 0);
        $set('rollover_remaining', 0);
        $set('active', 1);
        $set('status', 1);

        if (Schema::hasColumn('wallets', 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn('wallets', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existing) {
            unset($data['created_at']);
            DB::table('wallets')->where('user_id', $user->id)->update($data);
        } else {
            DB::table('wallets')->insert($data);
        }
    }
}
