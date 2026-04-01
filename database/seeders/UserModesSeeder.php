<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserModesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_modes = [
            [
                'name' => 'Citizen',
                'icon' => '/assets/icons/Citizen.svg',
                'is_global' => true,
            ],
            [
                'name' => 'Employee',
                'icon' => '/assets/icons/Employee.svg',
                'is_global' => false,
            ],
            [
                'name' => 'Event',
                'icon' => '/assets/icons/Event.svg',
                'is_global' => false,
            ],
                        [
                'name' => 'School',
                'icon' => '/assets/icons/School.svg',
                'is_global' => false,
            ],

        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Mode::truncate();

        foreach ($user_modes as $user_mode) {
            Mode::create($user_mode);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
