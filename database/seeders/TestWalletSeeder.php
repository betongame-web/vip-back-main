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

        if (Schema::hasColumn('wallets', 'user_id')) {
            $data['user_id'] = $user->id;
        }

        if (Schema::hasColumn('wallets', 'currency')) {
            $data['currency'] = 'USD';
        }

        if (Schema::hasColumn('wallets', 'symbol')) {
            $data['symbol'] = '$';
        }

        if (Schema::hasColumn('wallets', 'balance')) {
            $data['balance'] = 1000;
        }

        if (Schema::hasColumn('wallets', 'refer_rewards')) {
            $data['refer_rewards'] = 0;
        }

        if (Schema::hasColumn('wallets', 'total_balance')) {
            $data['total_balance'] = 1000;
        }

        if (Schema::hasColumn('wallets', 'total_deposit')) {
            $data['total_deposit'] = 1000;
        }

        if (Schema::hasColumn('wallets', 'total_withdrawal')) {
            $data['total_withdrawal'] = 0;
        }

        if (Schema::hasColumn('wallets', 'bonus_balance')) {
            $data['bonus_balance'] = 0;
        }

        if (Schema::hasColumn('wallets', 'status')) {
            $data['status'] = 1;
        }

        if (Schema::hasColumn('wallets', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('wallets', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existing) {
            unset($data['created_at']);

            DB::table('wallets')
                ->where('user_id', $user->id)
                ->update($data);
        } else {
            DB::table('wallets')->insert($data);
        }
    }
}