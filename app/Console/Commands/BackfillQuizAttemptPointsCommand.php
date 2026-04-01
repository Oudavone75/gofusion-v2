<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Console\Command;

class BackfillQuizAttemptPointsCommand extends Command
{
    protected $signature = 'quiz:backfill-attempt-points';

    protected $description = 'Backfill total_points and percentage for quiz attempts';

    public function handle(): int
    {
        $quizzes = Quiz::has('attempts')->get();

        if ($quizzes->isEmpty()) {
            $this->info('No quizzes with attempts found.');
            return self::SUCCESS;
        }

        $totalAttempts = QuizAttempt::whereIn('quiz_id', $quizzes->pluck('id'))
            ->where(function ($query) {
                $query->where('total_points', 0)->orWhereNull('total_points');
            })
            ->count();

        if ($totalAttempts === 0) {
            $this->info('All quiz attempts are already up to date.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalAttempts);
        $bar->start();

        $updatedCount = 0;

        foreach ($quizzes as $quiz) {
            $totalPoints = $quiz->points ?? 0;

            $attempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where(function ($query) {
                    $query->where('total_points', 0)->orWhereNull('total_points');
                })
                ->get();

            foreach ($attempts as $attempt) {
                $earnedPoints = $attempt->points ?? 0;
                $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

                $attempt->update([
                    'total_points' => $totalPoints,
                    'percentage' => $percentage,
                    'campaign_season_id' => $quiz?->campaign_season_id,
                ]);

                $updatedCount++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updatedCount} quiz attempt records.");

        return self::SUCCESS;
    }
}
