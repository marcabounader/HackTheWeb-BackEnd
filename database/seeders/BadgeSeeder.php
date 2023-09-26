<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labs = DB::table('labs')->whereNot('id','1')->get();

        foreach ($labs as $lab) {
            $categoryId = $lab->id % 3 + 1;

            DB::table('badges')->insert([
                'category_id' => $categoryId, 
                'name' => 'Badge'.$lab->id,
                'icon_url' => "http://192.168.1.29:8000/storage/badges/medal-$categoryId.svg",
                'lab_id' => $lab->id
            ]);
        }
    }
}
