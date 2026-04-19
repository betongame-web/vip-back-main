<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'banners' => [
                [
                    'id' => 1,
                    'title' => 'Main Banner',
                    'type' => 'carousel',
                    'image' => url('/assets/images/invite_bg_m.bafe1d0e.png'),
                    'link' => '/casinos',
                ],
                [
                    'id' => 2,
                    'title' => 'Home Banner',
                    'type' => 'home',
                    'image' => url('/assets/images/invite_bg_m.bafe1d0e.png'),
                    'link' => '/sports',
                ],
            ]
        ], 200);
    }
}