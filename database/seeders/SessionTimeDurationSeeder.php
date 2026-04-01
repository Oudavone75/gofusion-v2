<?php

namespace Database\Seeders;

use App\Models\SessionTimeDuration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SessionTimeDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = [
            [
                'title' => '3 sessions / semaine',
                'duration' => 3,
                'category' => 'Normal'
            ],
            [
                'title' => '6 sessions / semaine',
                'duration' => 6,
                'category' => 'Intensif'
            ],
            [
                'title' => '9 sessions / semaine',
                'duration' => 9,
                'category' => 'Extrême'
            ],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SessionTimeDuration::truncate();

        foreach ($sessions as $session) {
            SessionTimeDuration::create($session);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
