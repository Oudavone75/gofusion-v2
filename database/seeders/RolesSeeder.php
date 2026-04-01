<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'guard_name' => 'admin'],
            ['name' => 'Company Admin', 'guard_name' => 'web'],
            ['name' => 'User', 'guard_name' => 'web'],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();

        foreach ($roles as $role) {
            Role::create($role);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
