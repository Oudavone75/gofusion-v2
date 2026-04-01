<?php

namespace App\Services;

use App\Models\CampaignUserPerformance;
use App\Models\QuizAttempt;

class CampaignPerformanceService
{
    /**
     * Update quiz-level aggregated performance for a user in a campaign season.
     *
     * @param int $userId
     * @param int $campaignSeasonId
     * @return CampaignUserPerformance
     */
    public function updateQuizPerformance(int $userId, int $campaignSeasonId): CampaignUserPerformance
    {
        $attempts = QuizAttempt::where('user_id', $userId)
            ->where('campaign_season_id', $campaignSeasonId)
            ->get();

        $totalEarned = $attempts->sum('points');
        $totalPossible = $attempts->sum('total_points');
        $quizScore = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;

        $performance = CampaignUserPerformance::updateOrCreate(
            [
                'user_id' => $userId,
                'campaign_season_id' => $campaignSeasonId
            ],
            [
                'quiz_total_earned_points' => $totalEarned,
                'quiz_total_possible_points' => $totalPossible,
                'quiz_score_percentage' => round($quizScore, 2),
                'calculated_at' => now()
            ]
        );

        return $this->updateGlobalScore($performance);
    }

    /**
     * Update video-level performance for a user in a campaign season.
     * Ready to be called from a future VideoService.
     *
     * @param int $userId
     * @param int $campaignSeasonId
     * @param float $videoScorePercentage
     * @return CampaignUserPerformance
     */
    public function updateVideoPerformance(int $userId, int $campaignSeasonId): CampaignUserPerformance
    {
        $attempts = \App\Models\ImageSubmissionStep::where('user_id', $userId)
            ->where('total_points', '>', 0)
            ->whereHas('goSessionStep.goSession', function ($query) use ($campaignSeasonId) {
                $query->where('campaign_season_id', $campaignSeasonId);
            })
            ->whereHas('goSessionStep.imageSubmissionGuideline', function ($query) {
                // Ensure we only count challenges that are configured as "video" mode
                $query->where('mode', 'video');
            })
            ->get();

        $totalEarned = $attempts->sum('points');
        $totalPossible = $attempts->sum('total_points');
        $videoScorePercentage = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;

        $performance = CampaignUserPerformance::updateOrCreate(
            [
                'user_id' => $userId,
                'campaign_season_id' => $campaignSeasonId
            ],
            [
                'video_total_earned_points' => $totalEarned,
                'video_total_possible_points' => $totalPossible,
                'video_score_percentage' => round($videoScorePercentage, 2),
                'calculated_at' => now()
            ]
        );

        return $this->updateGlobalScore($performance);
    }

    /**
     * Recalculate and update the global score using configurable weights.
     *
     * @param CampaignUserPerformance $performance
     * @return CampaignUserPerformance
     */
    public function updateGlobalScore(CampaignUserPerformance $performance): CampaignUserPerformance
    {
        $quizWeight  = config('campaign_performance.quiz_weight', 0.5);
        $videoWeight = config('campaign_performance.video_weight', 0.5);

        $globalScore = ($performance->quiz_score_percentage * $quizWeight)
                     + ($performance->video_score_percentage * $videoWeight);

        $performance->update([
            'global_score_percentage' => round($globalScore, 2),
        ]);

        return $performance;
    }
}
