<?php

namespace App\Console\Commands;

use App\Models\ImageSubmissionGuideline;
use App\Models\ImageSubmissionStep;
use Illuminate\Console\Command;

class BackfillVideoSubmissionPointsCommand extends Command
{
    protected $signature = 'submissions:backfill-video-points';

    protected $description = 'Backfill total_points and percentage for video mode image submission steps';

    public function handle(): int
    {
        $guidelines = ImageSubmissionGuideline::where('mode', 'video')->get();

        if ($guidelines->isEmpty()) {
            $this->info('No video mode guidelines found.');
            return self::SUCCESS;
        }

        $stepIds = $guidelines->pluck('go_session_step_id');

        $totalSubmissions = ImageSubmissionStep::whereIn('go_session_step_id', $stepIds)
            ->where(function ($query) {
                $query->where('total_points', 0)->orWhereNull('total_points');
            })
            ->count();

        if ($totalSubmissions === 0) {
            $this->info('All video submissions are already up to date.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalSubmissions);
        $bar->start();

        $updatedCount = 0;

        foreach ($guidelines as $guideline) {
            $totalPoints = $guideline->points ?? 0;

            $submissions = ImageSubmissionStep::where('go_session_step_id', $guideline->go_session_step_id)
                ->where(function ($query) {
                    $query->where('total_points', 0)->orWhereNull('total_points');
                })
                ->get();

            foreach ($submissions as $submission) {
                $earnedPoints = $submission->points ?? 0;
                $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

                $submission->update([
                    'total_points' => $totalPoints,
                    'percentage' => $percentage,
                ]);

                $updatedCount++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updatedCount} video submission records.");

        return self::SUCCESS;
    }
}
