<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json([
            'setting' => [
                'site_name' => 'ViperPro',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'min_deposit' => 10,
                'max_deposit' => 10000,
                'min_withdrawal' => 20,
                'suitpay_is_enable' => true,
                'stripe_is_enable' => true,
                'rollover' => 1,
                'disable_spin' => false,
            ]
        ], 200);
    }
}