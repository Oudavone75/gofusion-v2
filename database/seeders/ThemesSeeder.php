<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names_list = [
            "Zero Hunger" => '🍽️ Bien manger, moins gaspiller',
            "Good Health and Well-being" => "💪 Prendre soin de soi",
            "Clean Water and Sanitation" => "🤝 Entraide",
            "Affordable and Clean Energy" => "⚡ Énergie & sobriété",
            "Responsible Consumption and Production" => "🛍️ Consommer autrement",
            "Climate Action" => "🌿 Climat & éco-gestes",
            "Life Below Water" => "💡 Innovation & idées durables",
            "Life on Land" => "🎯 Autres"
        ];

        $themes = [
            [
                'name' => 'Zero Hunger',
                'slug' => 'zero_hunger',
                'image' => '/assets/themes/2_zero_hunger.png',

            ],
            [
                'name' => 'Good Health and Well-being',
                'slug' => 'good_health_and_well_being',
                'image' => '/assets/themes/3_good_health_and_well_being.png',

            ],
            [
                'name' => 'Clean Water and Sanitation',
                'slug' => 'clean_water_and_sanitation',
                'image' => '/assets/themes/6_clean_water_and_sanitation.png',

            ],
            [
                'name' => 'Affordable and Clean Energy',
                'slug' => 'affortable_and_clean_energy',
                'image' => '/assets/themes/7_affordable_and_clean_energy.png',

            ],
            [
                'name' => 'Responsible Consumption and Production',
                'slug' => 'responsible_consumption_and_production',
                'image' => '/assets/themes/12_responsible_consumption_and_production.png',
            ],
            [
                'name' => 'Climate Action',
                'slug' => 'climate_action',
                'image' => '/assets/themes/13_climate_action.png',
            ],
            [
                'name' => 'Life Below Water',
                'slug' => 'life_below_water',
                'image' => '/assets/themes/14_life_below_water.png',
            ],
            [
                'name' => 'Life on Land',
                'slug' => 'life_on_land',
                'image' => '/assets/themes/15_life_on_land.png',
            ]
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('themes')->truncate();
        foreach ($themes as $key => $theme) {
            $theme['french_name'] = $names_list[$theme['name']];
                Theme::updateOrCreate(
                    [
                        'name' => $theme['name']
                    ],
                    $theme
                );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
