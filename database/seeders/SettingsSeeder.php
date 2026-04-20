<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $columns = Schema::getColumnListing('settings');

        $data = [
            'software_name' => 'ViperPro',
            'software_description' => 'ViperPro Gaming Platform',
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
            'bonus_vip' => 0,
            'activate_vip_bonus' => false,
            'suitpay_is_enable' => true,
            'stripe_is_enable' => true,
            'disable_spin' => false,
            'rollover' => 1,
            'language_default' => 'en',
            'maintenance_mode' => false,
        ];

        if (in_array('updated_at', $columns, true)) {
            $data['updated_at'] = now();
        }

        $payload = array_intersect_key($data, array_flip($columns));

        $exists = DB::table('settings')->first();

        if ($exists) {
            DB::table('settings')->update($payload);
            return;
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }

        DB::table('settings')->insert($payload);
    }
}