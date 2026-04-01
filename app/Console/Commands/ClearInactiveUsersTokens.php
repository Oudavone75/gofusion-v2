<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ClearInactiveUsersTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:clear-inactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tokens of users inactive for 3 months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $last_allowed_active_date = Carbon::now()->subMonths(3);

        $users = User::where('last_active_at', '<', $last_allowed_active_date)
            ->get();

        foreach ($users as $user) {
            $user->tokens()->delete();
        }

        $this->info("Inactive user tokens cleared successfully.");

        return 0;
    }
}
