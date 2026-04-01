<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\CompanyJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $companies = [
            [
                'name' => 'TechNova Solutions',
                'code' => 'TN001',
                'registration_date' => now()->subYears(2),
                'email' => 'contact@technova.com',
                'address' => '123 Innovation Park, Silicon Valley, CA 94043',
                'mode_id' => 2,
                'status' => 'active',
                'created_by' => 1
            ],
            [
                'name' => 'GreenLeaf Enterprises',
                'code' => 'GL002',
                'registration_date' => now()->subMonths(18),
                'email' => 'info@greenleaf.com',
                'address' => '456 Eco Blvd, Austin, TX 73301',
                'mode_id' => 3,
                'status' => 'active',
                'created_by' => 1
            ],
            [
                'name' => 'BlueOcean Tech',
                'code' => 'BO003',
                'registration_date' => now()->subYears(1)->subMonths(2),
                'email' => 'support@blueocean.com',
                'address' => '789 Marine Drive, Miami, FL 33101',
                'mode_id' => 4,
                'status' => 'active',
                'created_by' => 1
            ],
        ];
        $company_departments = [
            [
                'label' => 'Project Management',
                'slug' => 'project_manager'
            ],
            [
                'label' => 'UI/UX',
                'slug' => 'ui-ux-designer'
            ],
            [
                'label' => 'Back-End',
                'slug' => 'back-end-developer'
            ],
            [
                'label' => 'Front-End',
                'slug' => 'back-end-developer'
            ],
        ];
        $company_sessions = [
            [
                'title' => 'Company Session 1',
                'status' => 'active'
            ],
            [
                'title' => 'Company Session 2',
                'status' => 'pending'
            ],
            [
                'title' => 'Company Session 3',
                'status' => 'pending'
            ]
        ];
        Company::truncate();
        CompanyDepartment::truncate();
        foreach ($companies as $company) {
            $company_db = Company::updateOrCreate([
                'code' => $company['code']
            ], $company);
            if ($company_db->wasRecentlyCreated) {
                foreach ($company_departments as $department) {
                    CompanyDepartment::create([
                        'company_id' => $company_db->id,
                        'created_by' => 1,
                        'name' => $department['label'],
                        'status' => 'active'
                    ]);
                }
                // foreach ($company_sessions as $key => $company_session) {
                //     SessionTask::create([
                //         'title' => $company_db->name . ' Session '. ($key +1),
                //         'company_id' => $company_db->id,
                //         'status' => $company_session['status']
                //     ]);
                // }
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
