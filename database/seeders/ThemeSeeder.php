<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    public function run()
    {
        DB::table('themes')->insert([
            'name' => 'Orange Theme',
            'code' => 'igniter-orange',
            'version' => '1.0.0',
            'description' => 'Default theme for TastyIgniter',
            'data' => json_encode([]),
            'status' => 1,
            'is_default' => 1,
        ]);
    }
} 