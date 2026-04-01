<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignUserPerformance extends Model
{
    protected $guarded = ['id', '_token'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function campaignSeason()
    {
        return $this->belongsTo(CampaignsSeason::class, 'campaign_season_id');
    }

    /**
     * Calculate and update the global performance score for a user in a campaign.
     *
     * Weights are read from config/campaign_performance.php.
     * Can be called from QuizService, VideoService, or any other service.
     *
     * @param int $userId
     * @param int $campaignSeasonId
     * @return self
     */
    public static function calculateGlobalPerformance(int $userId, int $campaignSeasonId): self
    {
        $performance = self::where('user_id', $userId)
            ->where('campaign_season_id', $campaignSeasonId)
            ->first();

        if (!$performance) {
            return new self();
        }

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
