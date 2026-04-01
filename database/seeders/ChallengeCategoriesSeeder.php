<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ChallengeCategory;

class ChallengeCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ChallengeCategories = [
            [
                'name' => 'Image',
                'slug' => 'image_uploading',
                'status' => 'active',
                'created_by' => 1
            ],
            [
                'name' => 'Event',
                'slug' => 'attend_event',
                'status' => 'active',
                'created_by' => 1
            ],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ChallengeCategory::truncate();
        foreach ($ChallengeCategories as $category) {
            ChallengeCategory::updateOrCreate(
                [
                    'name' => $category['name']
                ],
                $category
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
