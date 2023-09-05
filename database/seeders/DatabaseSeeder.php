<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\BadgeCategory;
use App\Models\LabCategory;
use App\Models\LabDifficulty;
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

        LabCategory::create(['name' => 'SQLi']);
        LabCategory::create(['name' => 'XSS']);

        BadgeCategory::create(['name' => 'Gold']);
        BadgeCategory::create(['name' => 'Silver']);
        BadgeCategory::create(['name' => 'Bronze']);

        LabDifficulty::create(['name' => 'Hard']);
        LabDifficulty::create(['name' => 'Medium']);
        LabDifficulty::create(['name' => 'Easy']);
    }
}
