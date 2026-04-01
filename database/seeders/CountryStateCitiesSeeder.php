<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryStateCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Country::truncate();
        State::truncate();
        City::truncate();

        $countries_sql_file = file_get_contents(database_path('data/countries.sql'));
        DB::unprepared($countries_sql_file);
        $states_sql_file = file_get_contents(database_path('data/states.sql'));
        DB::unprepared($states_sql_file);
        $cities_sql_file = file_get_contents(database_path('data/cities.sql'));
        DB::unprepared($cities_sql_file);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
