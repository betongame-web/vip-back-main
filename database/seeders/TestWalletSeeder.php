<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestWalletSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('wallets')) {
            return;
        }

        $user = DB::table('users')->orderBy('id')->first();

        if (!$user) {
            return;
        }

        $columns = Schema::getColumnListing('wallets');

        $data = [
            'user_id' => $user->id,
            'balance' => 1000,
            'bonus_balance' => 0,
            'withdrawable_balance' => 0,
            'balance_bonus' => 0,
            'balance_withdrawal' => 0,
            'balance_bonus_rollover' => 0,
            'balance_deposit_rollover' => 0,
            'balance_demo' => 0,
            'refer_rewards' => 0,
            'vip_points' => 0,
            'vip_level' => 0,
            'currency' => 'USD',
            'symbol' => '$',
            'status' => true,
            'active' => true,
            'total_balance' => 1000,
        ];

        if (in_array('updated_at', $columns, true)) {
            $data['updated_at'] = now();
        }

        $payload = array_intersect_key($data, array_flip($columns));

        $existing = DB::table('wallets')->where('user_id', $user->id)->first();

        if ($existing) {
            DB::table('wallets')
                ->where('user_id', $user->id)
                ->update($payload);

            return;
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }

        DB::table('wallets')->insert($payload);
    }
}