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
        
        for ($i = 1; $i <= 19; $i++) {
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

        $users = DB::table('users')->where('type_id', 3)->get();

        foreach ($users as $user) {
                DB::table('labs')->insert([
                    'category_id' => 1, 
                    'difficulty_id' => 1,
                    'name' => 'SQLi user info ' . $user->id ,
                    'objective' => 'Insert an sqli in the login form to get all the user table information. Then look for the signature attribute of the admin which contains the flag and submit it. Good luck Hacker!',
                    'launch_api' => '/api/hacker/run-sqli-instance',
                    'reward' => 1,
                    'icon_url' => 'http://localhost:8000/storage/lab-icons/64fc07c581ecb.jpeg',
                ]);
                DB::table('labs')->insert([
                    'category_id' => 1,
                    'difficulty_id' => 2,
                    'name' => 'Command injection ' . $user->id,
                    'objective' => 'Get the flag from the flag.txt file using command injection into dns lookup.',
                    'launch_api' => '/api/hacker/run-ci-instance',
                    'reward' => 3,
                    'icon_url' => 'http://localhost:8000/storage/lab-icons/65097fced0a30.png',
                ]);
                DB::table('labs')->insert([
                    'category_id' => 10,
                    'difficulty_id' => 3,
                    'name' => 'Insecure JWT ' . $user->id,
                    'objective' => 'Leverage vulnerability in JWT handling to get the flag in the admin signature.',
                    'launch_api' => '/api/hacker/run-jwt-instance',
                    'reward' => 5,
                    'icon_url' => 'http://localhost:8000/storage/lab-icons/65097ff863638.jpeg',
                ]);
        }
    }
}
