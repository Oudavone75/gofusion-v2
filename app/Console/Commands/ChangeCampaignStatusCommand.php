<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CampaignsSeason;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\ProgressBar;

class ChangeCampaignStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-campaign-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating campaigns to "complete"...');
        $this->makeCampaignComplete();

        $this->info('Updating campaigns to "active"...');
        $this->makeCampaignActive();
    }

    private function makeCampaignComplete()
    {
        $today = Carbon::today();

        // Get the total number of campaigns that will be updated
        $totalCampaigns = CampaignsSeason::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->count();

        // Initialize the progress bar
        $progressBar = $this->output->createProgressBar($totalCampaigns);
        $progressBar->start();

        // Update campaigns in bulk
        CampaignsSeason::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->chunkById(100, function ($campaigns) use ($progressBar) {
                foreach ($campaigns as $campaign) {
                    // Perform the update for each campaign
                    $campaign->update(['status' => 'completed']);
                    $progressBar->advance();
                }
            });

        // Finish the progress bar
        $progressBar->finish();
        $this->newLine();
    }

    private function makeCampaignActive()
    {
        $today = Carbon::today();

        // Get the total number of campaigns that will be updated
        $totalCampaigns = CampaignsSeason::where('status', 'pending')
            ->whereDate('start_date', '=', $today)
            ->count();

        // Initialize the progress bar
        $progressBar = $this->output->createProgressBar($totalCampaigns);
        $progressBar->start();

        // Update campaigns in bulk
        CampaignsSeason::where('status', 'pending')
            ->whereDate('start_date', '=', $today)
            ->chunkById(100, function ($campaigns) use ($progressBar) {
                foreach ($campaigns as $campaign) {
                    // Perform the update for each campaign
                    $campaign->update(['status' => 'active']);
                    $progressBar->advance();
                }
            });

        // Finish the progress bar
        $progressBar->finish();
        $this->newLine();
    }
}
