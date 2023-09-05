<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'ContentEditor']);
        UserType::create(['name' => 'Normal']);

        UserType::create(['name' => 'Admin']);
        UserType::create(['name' => 'ContentEditor']);
        UserType::create(['name' => 'Normal']);
    }
}
