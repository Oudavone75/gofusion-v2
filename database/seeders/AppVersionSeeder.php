<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versions = [
            [
                'platform' => 'android',
                'latest_version' => '1.4.4+9',
                'min_supported_version' => '1.4.3+8',
                'force_update' => true,
                'update_url' => 'https://play.google.com/store/apps/details?id=com.gofusionapp.gofusionapp&hl=en',
            ],
            [
                'platform' => 'ios',
                'latest_version' => '1.4.4+9',
                'min_supported_version' => '1.4.3+8',
                'force_update' => false,
                'update_url' => 'https://apps.apple.com/fr/app/go-fusion-app/id6503088235',
            ],
        ];

        foreach ($versions as $version) {
            AppVersion::updateOrCreate(
                ['platform' => $version['platform']],
                $version
            );
        }
    }
}
