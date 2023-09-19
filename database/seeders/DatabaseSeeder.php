<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\BadgeCategory;
use App\Models\LabCategory;
use App\Models\LabDifficulty;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserType::create(['type' => 'Admin']);
        UserType::create(['type' => 'ContentEditor']);
        UserType::create(['type' => 'Normal']);

        LabCategory::create(['category' => 'Injection']);
        LabCategory::create(['category' => 'Server-Side Request Forgery']);
        LabCategory::create(['category' => 'Broken Access Control']);
        LabCategory::create(['category' => 'Cryptographic Failures']);
        LabCategory::create(['category' => 'Insecure Design']);
        LabCategory::create(['category' => 'Broken Access Control']);
        LabCategory::create(['category' => 'Security Misconfiguration']);
        LabCategory::create(['category' => 'Vulnerable and Outdate Components']);
        LabCategory::create(['category' => 'Identification and Authentication Failures']);
        LabCategory::create(['category' => 'Software and Data Integrity Failures']);
        LabCategory::create(['category' => 'Security Logging and Monitoring Failures']);



        BadgeCategory::create(['category' => 'Gold']);
        BadgeCategory::create(['category' => 'Silver']);
        BadgeCategory::create(['category' => 'Bronze']);

        LabDifficulty::create(['difficulty' => 'Hard']);
        LabDifficulty::create(['difficulty' => 'Medium']);
        LabDifficulty::create(['difficulty' => 'Easy']);

        User::create(['name' => 'admin', 'email' => "admin@admin.com", "password" => "adminadmin", "type_id" => "1"]);

    }
}
