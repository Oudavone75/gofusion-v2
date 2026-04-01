<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SessionTimeDurationSeeder::class,
            UserModesSeeder::class,
            RolesSeeder::class,
            AdminSeeder::class,
            CountryStateCitiesSeeder::class,
            // CompanySeeder::class,
            LanguagesSeeder::class,
            ChallengeCategoriesSeeder::class,
            ThemesSeeder::class,
            // CampaignSeasonSeeder::class,
            // NewsCategorySeeder::class,
            // NewsSeeder::class,
            RolePermissionSeeder::class,
            CompanyRolePermissionSeeder::class
        ]);
    }
}
