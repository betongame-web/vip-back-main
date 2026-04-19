<?php

namespace App\Http\Controllers\Api\Categories;

use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'categories' => [
                [
                    'id' => 1,
                    'name' => 'Slots',
                    'slug' => 'slots',
                    'image' => url('/assets/images/wager_1.6ec39cf4.png'),
                ],
                [
                    'id' => 2,
                    'name' => 'Originals',
                    'slug' => 'originals',
                    'image' => url('/assets/images/wager_2.8af53176.png'),
                ],
                [
                    'id' => 3,
                    'name' => 'Popular',
                    'slug' => 'popular',
                    'image' => url('/assets/images/wager_3.ee25b52f.png'),
                ],
            ]
        ], 200);
    }
}