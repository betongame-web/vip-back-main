<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
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
        ];

        foreach ([
            'software_description' => null,
            'software_logo_white' => null,
            'software_logo_black' => null,
            'decimal_format' => 'dot',
            'currency_position' => 'left',
            'prefix' => '$',
            'storage' => 'local',
            'max_withdrawal' => 10000,
            'initial_bonus' => 0,
            'bonus_vip' => 0,
            'activate_vip_bonus' => false,
        ] as $column => $value) {
            if (Schema::hasColumn('settings', $column)) {
                $data[$column] = $value;
            }
        }

        DB::table('settings')->updateOrInsert(['id' => 1], $data);
    }
}
