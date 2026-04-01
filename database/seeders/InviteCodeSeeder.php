<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;

class InviteCodeSeeder extends Seeder
{
    use AppCommonFunction;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to generate invite codes for existing users...');

        $users = User::whereNull('invite_code')->get();

        if ($users->isEmpty()) {
            $this->command->info('No users found without invite codes.');
            return;
        }

        $this->command->info("Found {$users->count()} users without invite codes.");

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                $user->invite_code = $this->generateUniqueInviteCode();
                $user->save();
                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->command->newLine();
            $this->command->info("✓ Successfully generated invite codes for {$users->count()} users!");
        } catch (\Exception $e) {
            DB::rollBack();
            $progressBar->finish();
            $this->command->newLine();
            $this->command->error('Failed to generate invite codes: ' . $e->getMessage());
        }
    }
}
