<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ProcessJobById extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-job {job-id : The ID of the job to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a specific job by its ID from the jobs table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $jobId = $this->argument('job-id');

        $job = DB::table('jobs')->where('id', $jobId)->first();

        if (!$job) {
            $this->error("Job with ID {$jobId} not found!");
            return 1;
        }

        $this->info("Processing job {$jobId}...");

        try {
            // Decode the payload
            $payload = json_decode($job->payload, true);

            // Unserialize the command
            $command = unserialize($payload['data']['command']);

            // Dispatch the job
            dispatch_sync($command);

            // Delete the job from queue
            DB::table('jobs')->where('id', $jobId)->delete();

            $this->info("Job {$jobId} processed successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to process job {$jobId}: " . $e->getMessage());
            return 1;
        }
    }
}
