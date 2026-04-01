<?php

namespace App\Services;

use App\Models\CampaignsSeason;
use App\Models\Reward;
use App\Models\UserScore;
use App\Models\UserTransaction;
use App\Models\CampaignsSeasonsRewardRange;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RewardService
{
    use AppCommonFunction;

    public function getCompleteCompanyCampaigns($company_id = null)
    {
        $campaignsSeasons = CampaignsSeason::where('status', config('constants.STATUS.COMPLETED'));
        if ($company_id) {
            $campaignsSeasons = $campaignsSeasons->where('company_id', $company_id);
        }
        return $campaignsSeasons->get();
    }

    public function getCompanyCampaigns($campaign_id)
    {
        return CampaignsSeason::find($campaign_id);
    }
    public function getCompanies()
    {
        return $this->getAllCompanies();
    }
    public function getEmployeeCampaignByCompany($type = 'personal', $campaign_id)
    {
        $campaign = CampaignsSeason::query()
            ->select('id', 'title', 'reward');

        if ($type === 'personal') {
            // Company-wide (no department restrictions)
            $campaign->whereNotNull('company_id')
                    ->whereDoesntHave('departments');
        } elseif ($type === 'department') {
            // Department-specific (exists in pivot table)
            $campaign->whereHas('departments');
        }
        $campaign->where('id', $campaign_id);
        $campaign->where('status', config('constants.STATUS.COMPLETED'));
        return $campaign->first();
    }

    public function getCampaignScoreCount($campaign_id)
    {
        return UserScore::where('campaign_season_id', $campaign_id)->count();
    }

    public function getUserLevel($user, $campaign_id, $total_users, $limit)
    {
        $levels = config('constants.LEVELS');
        $user_scores = UserScore::query()
            ->join('users', 'users.id', '=', 'user_scores.user_id')
            ->select(
                'user_scores.user_id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'user_scores.points',
                'user_scores.created_at',
                'users.image'
            )
            ->where('user_scores.campaign_season_id', $campaign_id)
            ->orderByDesc('user_scores.points')
            ->take($limit)
            ->get();

        $isRewardGiven = $this->isRewardGiven($campaign_id);
        if ($isRewardGiven) {
            $rewards = $this->getCampaignReward($campaign_id);
        } else {
            $rewards = $this->getRewardRanges($campaign_id);
        }


        $user_scores = $user_scores->map(function ($user, $index) use ($total_users, $levels, $rewards, $isRewardGiven) {
            // Calculate percentile based on total users
            $percentile = floor((($index + 1) / $total_users) * 100); // Percentile is calculated for all users

            // Assign the level based on the percentile
            $user->percentile = $percentile;
            $user->level = $levels[$percentile] ?? 'Starter 🌱';

            // Ensure the image URL is handled correctly
            $user->image = is_null($user->image) ? null : asset($user->image);

            // Rank
            $rank = $index + 1;
            $user->rank = $rank;

            // Rewards
            if ($isRewardGiven) {
                // Check if reward exists at this index
                $user->reward = isset($rewards[$index]) ? $rewards[$index]->amount : 0;
            } else {
                $reward = $rewards->first(function ($range) use ($rank) {
                    return $rank >= $range->rank_start && $rank <= $range->rank_end;
                });
                $user->reward = $reward ? $reward->reward : 0;
            }

            return $user;
        });

        return $user_scores;
    }

    public function getRewardRanges($campaign_id)
    {
        return CampaignsSeasonsRewardRange::where('campaign_season_id', $campaign_id)->orderBy('rank_start')->get();
    }


    public function isRewardGiven($campaign_id)
    {
        return Reward::where('campaign_season_id', $campaign_id)->where('status', config('constants.STATUS.APPROVED'))->exists();
    }

    public function getCampaignReward($campaign_id)
    {
        return Reward::where('campaign_season_id', $campaign_id)->get();
    }

    public function getDepartmentRankings($campaign_id)
    {
        $departmentRankings = DB::table('user_scores')
            ->join('company_departments as d', 'user_scores.company_department_id', '=', 'd.id')
            ->select(
                'user_scores.company_department_id',
                'd.name as department_name',
                DB::raw('SUM(user_scores.points) as total_points')
            )
            ->where('user_scores.campaign_season_id', $campaign_id)
            ->groupBy('user_scores.company_department_id', 'd.name')
            ->orderBy('total_points', 'desc')
            ->get();

        return $departmentRankings;
    }

    public function createBulkRewards($campaign, $user_ids, $rewards)
    {
        try {
            $reward_data = [];
            foreach ($user_ids as $key => $user_id) {
                $user = User::find($user_id);
                $reward_data[] = [
                    'campaign_season_id' => $campaign->id,
                    'user_id' => $user_id,
                    'amount' => $rewards[$key],
                    'company_id' => $campaign->company_id ?? null,
                    'company_department_id' => $user->company_department_id ?? null,
                    'status' => config('constants.STATUS.APPROVED'),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $transaction_data[] = [
                    'user_id' => $user_id,
                    'transaction_type' => config('constants.TRANSACTION_TYPE.CREDIT'),
                    'amount' => $rewards[$key],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            DB::beginTransaction();
            Reward::insert($reward_data);
            UserTransaction::insert($transaction_data);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeCustomRewards($request)
    {
        $campaign_season = CampaignsSeason::find($request->campaign_season_id);
        if ($campaign_season) {
            $campaign_season->custom_reward = $request->custom_reward;
            $campaign_season->save();
            return $campaign_season;
        }
        return false;
    }

    public function toggleCustomRewardStatus($campaign_season_id, $status = null)
    {
        $campaign_season = CampaignsSeason::find($campaign_season_id);
        if ($campaign_season) {
            if ($status) {
                $campaign_season->custom_reward_status = ($status === 'active');
            } else {
                $campaign_season->custom_reward_status = !$campaign_season->custom_reward_status;
            }
            $campaign_season->save();
            return true;
        }
        return false;
    }

    public function getCustomRewardsList($type = 'campaign', $company_id = null)
    {
        $query = CampaignsSeason::query()->with('company');

        if ($type == 'campaign') {
            $query->whereNotNull('company_id');
            if ($company_id) {
                $query->where('company_id', $company_id);
            }
        } else {
            $query->whereNull('company_id');
        }

        $query->where('end_date', '>=', date('Y-m-d'));

        $query->orderByRaw("CASE WHEN custom_reward_status = '1' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        return $query->paginate(10);
    }

    public function getUnrewardedCampaigns($company_id)
    {
        return CampaignsSeason::query()
            ->select('id', 'title')
            ->where('company_id', $company_id)
            ->whereNull('custom_reward')
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();
    }

    public function getUnrewardedSeasons()
    {
        return CampaignsSeason::query()
            ->select('id', 'title')
            ->whereNull('company_id')
            ->whereNull('custom_reward')
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();
    }
}
