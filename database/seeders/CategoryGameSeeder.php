<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryGameSeeder extends Seeder
{
    public function run(): void
    {
        $allGameIds = [101, 102, 103, 104, 105, 106, 201, 202, 203, 204, 205, 206];

        foreach ($allGameIds as $gameId) {
            DB::table('category_game')->updateOrInsert(
                [
                    'category_id' => 1,
                    'game_id' => $gameId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('category_game')->updateOrInsert(
                [
                    'category_id' => 2,
                    'game_id' => $gameId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ([101, 102, 201] as $gameId) {
            DB::table('category_game')->updateOrInsert(
                [
                    'category_id' => 3,
                    'game_id' => $gameId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}