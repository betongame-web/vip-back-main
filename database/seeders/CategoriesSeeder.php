<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Slots',
                'slug' => 'slots',
                'image' => '/assets/images/wager_1.6ec39cf4.png',
                'status' => true,
                'views' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('categories')->updateOrInsert(
            ['id' => 2],
            [
                'name' => 'Originals',
                'slug' => 'originals',
                'image' => '/assets/images/wager_2.8af53176.png',
                'status' => true,
                'views' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('categories')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Popular',
                'slug' => 'popular',
                'image' => '/assets/images/wager_3.ee25b52f.png',
                'status' => true,
                'views' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}