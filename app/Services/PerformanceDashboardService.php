<?php

namespace App\Services;

use App\Models\CampaignsSeason;
use App\Models\CampaignUserPerformance;
use App\Models\ImageSubmissionStep;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class PerformanceDashboardService
{
    /**
     * Get dashboard summary stats for a campaign season.
     * Pass $companyId to scope for company admin.
     */
    public function getDashboardStats(int $campaignSeasonId, ?int $companyId = null, int $page = 1, int $perPage = 10): array
    {
        $baseQuery = CampaignUserPerformance::where('campaign_season_id', $campaignSeasonId);

        if ($companyId) {
            $baseQuery->whereHas('user', fn($q) => $q->where('company_id', $companyId));
        }

        // Aggregates from all records (not just current page)
        $totalEmployees = (clone $baseQuery)->count();
        $avgQuizScore = $totalEmployees > 0 ? round((clone $baseQuery)->avg('quiz_score_percentage'), 2) : 0;
        $avgVideoScore = $totalEmployees > 0 ? round((clone $baseQuery)->avg('video_score_percentage'), 2) : 0;
        $avgGlobalScore = $totalEmployees > 0 ? round((clone $baseQuery)->avg('global_score_percentage'), 2) : 0;

        // Score distribution from all records
        $scoreDistribution = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        (clone $baseQuery)->select('global_score_percentage')->each(function ($p) use (&$scoreDistribution) {
            $score = $p->global_score_percentage ?? 0;
            if ($score <= 20) $scoreDistribution['0-20']++;
            elseif ($score <= 40) $scoreDistribution['21-40']++;
            elseif ($score <= 60) $scoreDistribution['41-60']++;
            elseif ($score <= 80) $scoreDistribution['61-80']++;
            else $scoreDistribution['81-100']++;
        });

        // Paginated employee list
        $paginated = (clone $baseQuery)
            ->with(['user.department'])
            ->orderBy('global_score_percentage', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $employees = $paginated->map(function ($p) {
            return [
                'id' => $p->user?->id,
                'name' => $p->user ? ($p->user->first_name . ' ' . $p->user->last_name) : 'N/A',
                'department' => $p->user?->department?->name ?? 'N/A',
                'job_title' => $p->user?->job_title ?? 'N/A',
                'quiz_score' => round($p->quiz_score_percentage ?? 0, 2),
                'video_score' => round($p->video_score_percentage ?? 0, 2),
                'global_score' => round($p->global_score_percentage ?? 0, 2),
            ];
        })->values();

        return [
            'avgQuizScore' => $avgQuizScore,
            'avgVideoScore' => $avgVideoScore,
            'avgGlobalScore' => $avgGlobalScore,
            'totalEmployees' => $totalEmployees,
            'scoreDistribution' => $scoreDistribution,
            'employees' => $employees,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem() ?? 0,
                'to' => $paginated->lastItem() ?? 0,
            ],
        ];
    }

    /**
     * Get detailed performance data for a specific employee in a campaign.
     */
    public function getEmployeeDetail(int $userId, int $campaignSeasonId, int $quizPage = 1, int $videoPage = 1, int $perPage = 10): array
    {
        $user = User::with('department')->find($userId);
        $campaign = CampaignsSeason::find($campaignSeasonId);

        $performance = CampaignUserPerformance::where('user_id', $userId)
            ->where('campaign_season_id', $campaignSeasonId)
            ->first();

        // Quiz attempts (paginated)
        $quizAttempts = QuizAttempt::with(['quiz.session'])
            ->where('user_id', $userId)
            ->where('campaign_season_id', $campaignSeasonId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'quiz_page', $quizPage);

        // Video submissions (paginated)
        $videoSubmissions = ImageSubmissionStep::with([
            'goSessionStep.goSession',
            'goSessionStep.imageSubmissionGuideline',
        ])
            ->where('user_id', $userId)
            ->whereHas('goSessionStep.goSession', fn($q) => $q->where('campaign_season_id', $campaignSeasonId))
            ->whereHas('goSessionStep.imageSubmissionGuideline', fn($q) => $q->where('mode', 'video'))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'video_page', $videoPage);

        return [
            'user' => $user,
            'campaign' => $campaign,
            'performance' => $performance ? [
                'quiz_score' => round($performance->quiz_score_percentage ?? 0, 2),
                'video_score' => round($performance->video_score_percentage ?? 0, 2),
                'global_score' => round($performance->global_score_percentage ?? 0, 2),
                'quiz_earned' => $performance->quiz_total_earned_points ?? 0,
                'quiz_total' => $performance->quiz_total_possible_points ?? 0,
                'video_earned' => $performance->video_total_earned_points ?? 0,
                'video_total' => $performance->video_total_possible_points ?? 0,
            ] : null,
            'quizAttempts' => $quizAttempts,
            'videoSubmissions' => $videoSubmissions,
        ];
    }

    /**
     * Get the most recent active campaign season, optionally scoped to company.
     */
    public function getActiveCampaign(?int $companyId = null): ?CampaignsSeason
    {
        $query = CampaignsSeason::where('status', 'active')
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->first();
    }

    /**
     * Get all campaign seasons for dropdown, optionally scoped to company.
     * @param string $type 'campaign' (has company_id) or 'season' (no company_id)
     */
    public function getAllCampaigns(?int $companyId = null, string $type = 'campaign'): Collection
    {
        $query = CampaignsSeason::orderBy('created_at', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            if ($type === 'campaign') {
                $query->whereNotNull('company_id');
            } else {
                $query->whereNull('company_id');
            }
        }

        return $query->get();
    }
}
