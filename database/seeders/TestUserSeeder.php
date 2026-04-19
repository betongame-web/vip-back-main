<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'test@viperpro.com';
        $passwordPlain = '12345678';

        $existing = DB::table('users')->where('email', $email)->first();

        $data = [];

        if (Schema::hasColumn('users', 'name')) {
            $data['name'] = 'Test User';
        }

        if (Schema::hasColumn('users', 'username')) {
            $data['username'] = 'testuser';
        }

        if (Schema::hasColumn('users', 'email')) {
            $data['email'] = $email;
        }

        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = Hash::make($passwordPlain);
        }

        if (Schema::hasColumn('users', 'status')) {
            $data['status'] = 1;
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $data['email_verified_at'] = now();
        }

        if (Schema::hasColumn('users', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existing) {
            unset($data['created_at']);

            DB::table('users')
                ->where('email', $email)
                ->update($data);
        } else {
            DB::table('users')->insert($data);
        }
    }
}