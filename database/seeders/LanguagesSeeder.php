<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'label' => 'en',
                'slug'  => 'en',
                'flag'  => 'https://flagcdn.com/w320/gb.png'
            ],
            [
                'label' => 'fr',
                'slug'  => 'fr',
                'flag'  => 'https://flagcdn.com/w320/fr.png'
            ]
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Language::truncate();
        foreach ($languages as $language) {
            Language::create($language);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
