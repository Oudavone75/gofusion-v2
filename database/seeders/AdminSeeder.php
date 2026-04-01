<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'testing', 'dev'])) {
            $email = 'admin@gofusion.com';
            $password = 'password';
        } else {
            $email = 'oudavone.surot@gofusion.fr';
            $password = 'QjSNNkARupqxYV4r';
        }

        $admin = Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Go Fusion Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]
        );

        if ($admin) {
            $admin->assignRole(config('constants.ROLES.ADMIN'));
        }
    }
}
