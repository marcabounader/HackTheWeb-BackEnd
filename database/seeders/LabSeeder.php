<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->where('type_id', 3)->get();

        foreach ($users as $user) {
                DB::table('labs')->insert([
                    'category_id' => 1, 
                    'difficulty_id' => 1,
                    'name' => 'SQLi user info ' . $user->id ,
                    'objective' => 'Insert an sqli in the login form to get all the user table information. Then look for the signature attribute of the admin which contains the flag and submit it. Good luck Hacker!',
                    'launch_api' => '/api/hacker/run-sqli-instance',
                    'reward' => 1,
                    'icon_url' => "http://" . getenv('APP_IP') . ":8000/storage/lab-icons/sqli.jpeg",
                ]);
                DB::table('labs')->insert([
                    'category_id' => 1,
                    'difficulty_id' => 2,
                    'name' => 'Command injection ' . $user->id,
                    'objective' => 'Get the flag from the flag.txt file using command injection into dns lookup.',
                    'launch_api' => '/api/hacker/run-ci-instance',
                    'reward' => 3,
                    'icon_url' => "http://" . getenv('APP_IP') . ":8000/storage/lab-icons/ci.png",
                ]);
                DB::table('labs')->insert([
                    'category_id' => 10,
                    'difficulty_id' => 3,
                    'name' => 'Insecure JWT ' . $user->id,
                    'objective' => 'Leverage vulnerability in JWT handling to get the flag in the admin signature.',
                    'launch_api' => '/api/hacker/run-jwt-instance',
                    'reward' => 5,
                    'icon_url' => "http://" . getenv('APP_IP') . ":8000/storage/lab-icons/jwt.jpeg",
                ]);
            }
    }
}
