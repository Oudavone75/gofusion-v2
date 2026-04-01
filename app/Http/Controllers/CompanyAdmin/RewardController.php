<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAdmin\RewardRequest;
use App\Services\RewardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;


class RewardController extends Controller
{
    use ApiResponse;
    public function __construct(public RewardService $reward_service){}

    public function index()
    {
        $company_id = auth()->user()->company_id;
        $campaigns = $this->reward_service->getCompleteCompanyCampaigns($company_id);
        return view('company_admin.rewards.index', compact('campaigns'));
    }

    public function view($campaign,$type='personal')
    {
        $user = auth()->user();
        $campaign = $this->reward_service->getEmployeeCampaignByCompany($type,$campaign);
        $total_users = $this->reward_service->getCampaignScoreCount($campaign->id);
        $is_reward_given = $this->reward_service->isRewardGiven($campaign->id);
        $users_with_levels = $this->reward_service->getUserLevel($user,$campaign->id,$total_users,$campaign->max_user_ranking_size);
        $campaign_rewards = $this->reward_service->getCampaignReward($campaign->id);
        $departmentRankings = $this->reward_service->getDepartmentRankings($campaign->id);

        return view('company_admin.rewards.view', compact('campaign','users_with_levels','is_reward_given','campaign_rewards','departmentRankings'));
    }

    public function store($campaign, RewardRequest $request)
    {
        try {
            $campaign = $this->reward_service->getCompanyCampaigns($campaign);
            // $total_given_reward = array_sum($request->input('reward'));
            // if($total_given_reward > $campaign->reward){
            //     return $this->error(status: false, message: 'Total reward is exceeded than the campaign reward.', code: 500);
            // }
            $user_ids = $request->input('user_id');
            $rewards = $request->input('reward');
            $reward =$this->reward_service->createBulkRewards($campaign, $user_ids, $rewards);
            if($reward){
                return $this->success(status: true, message: 'Reward has been given successfully!', code: 200);
            }
            return $this->error(status: false, message: 'Failed to give reward.', code: 500);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function customRewardsList(Request $request)
    {
        $company_id = auth()->user()->company_id;
        $campaigns = $this->reward_service->getCustomRewardsList('campaign', $company_id);
        return view('company_admin.rewards.custom-rewards.index', compact('campaigns'));
    }

    public function customRewardsCreate()
    {
        $company_id = auth()->user()->company_id;
        $campaigns = $this->reward_service->getUnrewardedCampaigns($company_id);
        return view('company_admin.rewards.custom-rewards.create', compact('campaigns'));
    }

    public function customRewardsStore(Request $request)
    {
        try {
            $request->validate([
                'campaign_season_id' => 'required|exists:campaigns_seasons,id',
                'custom_reward' => 'required|string|max:1000',
            ]);
            $reward = $this->reward_service->storeCustomRewards($request);
            if ($reward) {
                return $this->success(status: true, message: 'Custom rewards have been given successfully!', code: 200, result: [
                    'redirect_url' => route('company_admin.rewards.custom.index')
                ]);
            }
            return $this->error(status: false, message: 'Failed to give custom rewards.', code: 500);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function customRewardsView($id)
    {
        $campaign_season = $this->reward_service->getCompanyCampaigns($id);
        return view('company_admin.rewards.custom-rewards.view', compact('campaign_season'));
    }

    public function customRewardsEdit($id)
    {
        $campaign_season = $this->reward_service->getCompanyCampaigns($id);
        $campaigns = $this->reward_service->getCompleteCompanyCampaigns($campaign_season->company_id);
        return view('company_admin.rewards.custom-rewards.edit', compact('campaign_season', 'campaigns'));
    }

    public function toggleCustomRewardStatus(Request $request, $campaign_season_id)
    {
        try {
            $reward = $this->reward_service->toggleCustomRewardStatus($campaign_season_id, $request->status);
            if ($reward) {
                return $this->success(status: true, message: 'Custom reward status has been updated successfully!', code: 200);
            }
            return $this->error(status: false, message: 'Failed to update custom reward status.', code: 500);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }
}
