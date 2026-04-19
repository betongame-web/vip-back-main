<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvidersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('providers')->updateOrInsert(
            ['id' => 1],
            [
                'code' => 'original-slots-a',
                'name' => 'Original Slots A',
                'rtp' => 96.50,
                'status' => true,
                'distribution' => 'source',
                'views' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('providers')->updateOrInsert(
            ['id' => 2],
            [
                'code' => 'original-slots-b',
                'name' => 'Original Slots B',
                'rtp' => 96.20,
                'status' => true,
                'distribution' => 'source',
                'views' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}