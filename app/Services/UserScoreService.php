<?php

namespace App\Services;

use App\Http\Resources\UserScoreResource;
use App\Models\SpinWheel;
use App\Models\GoSession;
use App\Models\CampaignsSeason;
use App\Models\GoUserProgress;
use App\Models\User;
use App\Models\UserLeaveTransaction;
use App\Models\UserScore;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserScoreService
{
    use AppCommonFunction;
    public function __construct(private UserScore $user_score, private GoUserProgress $go_user_progress, private UserLeaveTransaction $user_leave_transaction) {}

    /**
     * Reconcile orphan points (those with NULL campaign_season_id) into a specific campaign season.
     */
    public function reconcileOrphanPoints($user_id, $target_campaign_season_id, $company_id = null, $department_id = null)
    {
        if (!$target_campaign_season_id) {
            return;
        }

        // Find points from orphan records (NULL campaign_season_id)
        $orphanScore = UserScore::where('user_id', $user_id)
            ->whereNull('campaign_season_id')
            ->where('company_id', $company_id)
            ->where('company_department_id', $department_id)
            ->first();

        if ($orphanScore && $orphanScore->points > 0) {
            $orphanPoints = $orphanScore->points;

            // Find or create the score record for the active campaign
            $seasonScore = UserScore::firstOrCreate([
                'user_id' => $user_id,
                'campaign_season_id' => $target_campaign_season_id,
                'company_id' => $company_id,
                'company_department_id' => $department_id,
            ]);

            $seasonScore->points += $orphanPoints;
            $seasonScore->save();

            // Delete the orphan record after reconciliation
            $orphanScore->delete();
        }
    }

    public function getUserScoreObject($go_session_step_id = null, $bonus_type = null, $user_id = null)
    {
        $column = null;
        $score = null;
        $company_id = null;
        $company_department_id = null;

        if ($user_id !== null) {
            $user = User::select('company_id', 'company_department_id')->find($user_id);

            if (!$user) {
                return response()->json(['error' => 'User not found']);
            }

            $column = 'leaves';
            $score = 1;
            $company_id = $user->company_id;
            $company_department_id = $user->company_department_id;
        } else {
            if (!$go_session_step_id || !$bonus_type) {
                return response()->json(['error' => 'Missing required parameters'], 400);
            }

            $spinWheel = SpinWheel::select($bonus_type)->find($go_session_step_id);

            if (!$spinWheel) {
                return response()->json(['error' => 'SpinWheel record not found'], 400);
            }

            $score = $spinWheel->$bonus_type;
            $column = ($bonus_type === 'bonus_leaves') ? 'leaves' : 'points';

            $go_session = GoSession::select('campaign_season_id')->find($go_session_step_id);

            if (!$go_session) {
                return response()->json(['error' => 'GoSession not found'], 400);
            }

            $campaignSeason = CampaignsSeason::select('company_id', 'company_department_id')
                ->find($go_session->campaign_season_id);

            if (!$campaignSeason) {
                return response()->json(['error' => 'Campaign Season not found'], 400);
            }

            $company_id = $campaignSeason->company_id;
            $company_department_id = $campaignSeason->company_department_id;
        }

        return [
            'user_id' => auth()->id() ?? $user_id,
            $column => $score,
            'company_id' => $company_id,
            'company_department_id' => $company_department_id,
        ];
        return $response;
    }

    public function getUserScoresAndLeaves($request = [], $user)
    {
        $company_id = $user->company_id;
        $company_department_id = $user->company_department_id;
        $user_score = $this->user_score->where('campaign_season_id', $request['campaign_season_id'])
            ->where('user_id', $user->id)
            ->when($user->isEmployee() && !empty($company_id), function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })->when($user->isEmployee() && !empty($company_department_id), function ($q) use ($company_department_id) {
                $q->where(function ($query) use ($company_department_id) {
                    $query->orWhere('company_department_id', $company_department_id);
                });
            })->first();
        if (!$user_score) {
            return collect([
                'leaves' => 0,
                'points' => 0
            ]);
        }
        return new UserScoreResource($user_score);
    }

    public function getTotalLeaves($user)
    {
        return $this->user_leave_transaction->where('user_id', $user->id)->sum('amount');
    }

    public function getUserLastAttemptedStep($request, $user)
    {
        $last_attempted_step = $this->go_user_progress::select('campaigns_season_id as campaign_season_id', 'go_session_id', 'go_session_step_id')
            ->where('campaigns_season_id', $request['campaign_season_id'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->first();
        return $last_attempted_step;
    }


    public function getUserRanking($request, $user)
    {
        $campaign_season_id = $request['campaign_season_id'];
        $sub = UserScore::query()
            ->select([
                'user_id',
                'company_id',
                'company_department_id',
                'campaign_season_id',

                // Season points & rank
                'points',
                DB::raw('RANK() OVER (
            PARTITION BY campaign_season_id
            ORDER BY points DESC, created_at ASC
        ) AS season_rank'),

                // Company points & rank
                DB::raw('CASE WHEN company_id IS NOT NULL
            THEN points ELSE 0 END AS company_points'),
                DB::raw('RANK() OVER (
            PARTITION BY campaign_season_id, company_id
            ORDER BY points DESC, created_at ASC
        ) AS company_rank'),

                // Department points & rank
                DB::raw('CASE WHEN company_department_id IS NOT NULL
            THEN points ELSE 0 END AS department_points'),
                DB::raw('RANK() OVER (
            PARTITION BY campaign_season_id, company_id, company_department_id
            ORDER BY points DESC, created_at ASC
        ) AS department_rank'),
            ])
            ->where('campaign_season_id', $campaign_season_id);

        $row = DB::query()
            ->fromSub($sub, 'r')
            ->where('r.user_id', $user->id)
            ->first();
        if (!$row) {
            return [
                'campaign_or_season_wise_raking' => ['points' => (int)0, 'rank' => (int)0],
                'company_wise_ranking'           => ['points' => (int)0, 'rank' => (int)0],
                'department_wise_ranking'        => ['points' => (int)0, 'rank' => (int)(0)],
            ];
        }
        return [
            'campaign_or_season_wise_raking' => [
                'points' => (int) $row->points,
                'rank'   => (int) $row->season_rank,
            ],
            'company_wise_ranking' => [
                'points' => $row->company_points > 0 ? (int) $row->points : 0,
                'rank'   => $row->company_points > 0 ? (int) $row->company_rank : 0,
            ],
            'department_wise_ranking' => [
                'points' => $row->department_points > 0 ? (int) $row->points : 0,
                'rank'   => $row->department_points > 0 ? (int) $row->department_rank : 0,
            ],
        ];
        // return [
        //     'campaign_or_season_wise_raking' => ['points' => (int)$row->points, 'rank' => (int)$row->season_rank],
        //     'company_wise_ranking'           => ['points' => (int)$row->points, 'rank' => (int)($row->company_rank ?? 0)],
        //     'department_wise_ranking'        => ['points' => (int)$row->points, 'rank' => (int)($row->department_rank ?? 0)],
        // ];
    }

    public function getUserLevel($levels, $campaign_season, $user, $isArray = false)
    {
        // $user_score = DB::table('user_scores')
        //     ->join('users', 'users.id', '=', 'user_scores.user_id')
        //     ->select(
        //         'user_scores.user_id',
        //         'users.first_name',
        //         'users.last_name',
        //         'users.email',
        //         'user_scores.points',
        //         'user_scores.created_at',
        //         'users.image',
        //         // DB::raw('NTILE(10) OVER (ORDER BY user_scores.points DESC, user_scores.created_at ASC) as percentile')
        //         DB::raw('RANK() OVER (ORDER BY user_scores.points DESC, user_scores.created_at ASC) as percentile')
        //     )
        //     ->where('user_scores.campaign_season_id', $campaign_season->id)
        //     ->where('user_scores.user_id', $user->id)
        //     ->orderByDesc('user_scores.points')
        //     ->first();
        $rankedQuery = DB::table('user_scores')
            ->join('users', 'users.id', '=', 'user_scores.user_id')
            ->select(
                'user_scores.user_id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'user_scores.points',
                'user_scores.created_at',
                'users.image',
                DB::raw('RANK() OVER (ORDER BY user_scores.points DESC, user_scores.created_at ASC) as percentile')
            )
            ->where('user_scores.campaign_season_id', $campaign_season->id);

        $user_score = DB::table(DB::raw("({$rankedQuery->toSql()}) as ranked_scores"))
            ->mergeBindings($rankedQuery)
            ->where('user_id', $user->id)
            ->first();
        $level = config('constants.LEVELS.10');
        if ($user_score && $user_score->points > 0) {
            $level = $levels[$user_score->percentile] ?? config('constants.LEVELS.10');
        }
        if ($isArray) {
            $levels = config('constants.LEVELS');
            $levelNumber = array_search($level, $levels);
            $icon = mb_substr($level, -1);
            return [
                'level' => $level,
                'level_percentile' => $user_score ? (int) $user_score->percentile : 10,
                'points' => $user_score ? (int) $user_score->points : 0,
                'level_number' => $levelNumber !== false ? $levelNumber : 10,
                'icon' => $icon,
            ];
        }
        return $level;
    }

    public function getUserLevelDetails($campaign_season, $user)
    {
        $levels = config('constants.LEVELS');

        if (!$campaign_season) {
            return [
                'current_level' => $levels[10],
                'current_points' => 0,
                'next_goals' => [],
            ];
        }

        // Get top 10 users' points (these define level thresholds)
        $topScores = DB::table('user_scores')
            ->select('points')
            ->where('campaign_season_id', $campaign_season->id)
            ->where('points', '>', 0)
            ->orderByDesc('points')
            ->orderBy('created_at')
            ->limit(10)
            ->pluck('points')
            ->values()
            ->toArray();
        while (count($topScores) < 10) {
            $topScores[] = 0;
        }
        // Get current user's score
        $userScore = DB::table('user_scores')
            ->where('campaign_season_id', $campaign_season->id)
            ->where('user_id', $user->id)
            ->first();

        $userPoints = $userScore ? (int) $userScore->points : 0;

        // Determine user's current level via rank
        $currentLevel = $this->getUserLevel($levels, $campaign_season, $user, true);

        // Build next_goals: all levels except Starter (rank 10)
        $next_goals = [];
        foreach ($topScores as $index => $points) {
            $rank = $index + 1;
            $levelName = $levels[$rank] ?? null;
            if (!$levelName || $rank >= 10) {
                continue;
            }
            $pointsNeeded = max(0, (int) $points - $userPoints);
            $next_goals[] = [
                'level' => mb_substr($levelName, 0, -2), // Remove emoji for display
                'points_needed' => $points,
                'is_achieved' => $userPoints !== 0 && $userPoints >= (int) $points,
                'level_number' => $rank,
                'icon' => $rank == 7 ? '♻️' : mb_substr($levelName, -1),
                'description' => $userPoints >= (int) $points ? trans('general.level_up', ['level' => mb_substr($levelName, 0, -2)])
                    : trans('general.level_down', ['level' => mb_substr($levelName, 0, -2), 'points' => $pointsNeeded]),
            ];
        }

        // Reverse so lowest next goal comes first
        $next_goals = array_reverse($next_goals);
        return [
            'current_level' => mb_substr($currentLevel['level'], 0, -2), // Remove emoji for display
            'current_points' => $userPoints,
            'current_level_number' => $currentLevel['level_number'],
            'icon' => $currentLevel['icon'],
            'next_goals' => $next_goals,
        ];
    }

    public function getUsersWithLevels($levels, $campaign_season, $company_department_id, $isArray = false)
    {
        $query = DB::table('user_scores')
            ->join('users', 'users.id', '=', 'user_scores.user_id')
            ->select(
                'user_scores.user_id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'user_scores.points',
                'user_scores.created_at',
                'users.image',
                // DB::raw('ROW_NUMBER() OVER (ORDER BY user_scores.points DESC, user_scores.created_at ASC) as percentile')
            );
        // if ($isArray) {
        //     $query = $query->whereIn('user_scores.campaign_season_id', $campaign_season);
        // } else {
        //     $query = $query->where('user_scores.campaign_season_id', $campaign_season->id);
        // }
        if ($isArray) {
            $campaign_season = is_array($campaign_season) ? $campaign_season : [$campaign_season];
            if (count($campaign_season) === 1) {
                $query = $query->where('user_scores.campaign_season_id', $campaign_season[0]);
            } else {
                $query = $query->whereIn('user_scores.campaign_season_id', $campaign_season);
            }
        } else {
            $query = $query->where('user_scores.campaign_season_id', $campaign_season->id);
        }
        $query = $query
            ->where('user_scores.points', '>', 0)
            ->orderByDesc('user_scores.points');
        if ($company_department_id !== null) {
            $query = $query->where('user_scores.company_department_id', $company_department_id);
        }
        return   $query->limit(100)
            ->get()
            ->map(function ($user, $index) use ($levels) {
                $user->percentile = $index + 1;
                $user->level = $levels[$user->percentile] ?? config('constants.LEVELS.10');
                $user->image = is_null($user->image) ? null : asset($user->image);
                $user->points = (string) ((int) $user->points);
                return $user;
            });
    }
}
