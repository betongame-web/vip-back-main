<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            ProvidersSeeder::class,
            CategoriesSeeder::class,
            GamesSeeder::class,
            CategoryGameSeeder::class,
            TestUserSeeder::class,
            TestWalletSeeder::class,
        ]);
    }
}