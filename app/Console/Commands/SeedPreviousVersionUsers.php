<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedPreviousVersionUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:previous-users
                            {filePath? : Path to the Excel file}
                            {--file-path= : Path to the Excel file (alternative)}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed previous version users with optional file path';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file-path') ?? public_path('Joueurs_V1.xlsx');
        dd($filePath);
        $this->call('db:seed', [
            '--class' => 'PreviousVersionUsersSeeder',
            '--filePath' => $filePath
        ]);

        return Command::SUCCESS;
    }
}
