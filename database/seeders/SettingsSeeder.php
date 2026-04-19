<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            [
                'software_name' => 'ViperPro',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'min_deposit' => 10,
                'max_deposit' => 10000,
                'min_withdrawal' => 20,
                'suitpay_is_enable' => true,
                'stripe_is_enable' => true,
                'disable_spin' => false,
                'rollover' => 1,
                'language_default' => 'en',
                'maintenance_mode' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}