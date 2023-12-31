<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        for ($i = 1; $i <= 500; $i++) {
            DB::table('users')->insert([
                'type_id' => 3,
                'name' => 'hacker ' . $i,
                'email' => 'hacker' . $i . '@example.com',
                'password' => Hash::make('hackerhacker'),
                'profile_url' => null,
                'rewards' => null,
                'is_restricted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
    }
}
