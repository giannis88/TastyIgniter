<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('admin_users')->insert([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'super_user' => 1,
            'status' => 1,
            'is_activated' => 1,
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 