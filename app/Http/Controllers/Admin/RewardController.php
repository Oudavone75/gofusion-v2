<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RewardRequest;
use App\Services\RewardService;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    use ApiResponse, AppCommonFunction;
    public function __construct(public RewardService $reward_service){}

    public function index(Request $request)
    {
        $companies = $this->reward_service->getCompanies();
        $campaigns = $this->reward_service->getCompleteCompanyCampaigns($request->company_id);
        return view('admin.rewards.index', compact('campaigns', 'companies'));
    }

    public function view($campaign,$type='citizen')
    {
        $user = auth()->user();
        $campaign = $this->reward_service->getEmployeeCampaignByCompany($type,$campaign);
        $total_users = $this->reward_service->getCampaignScoreCount($campaign->id);
        $is_reward_given = $this->reward_service->isRewardGiven($campaign->id);
        $users_with_levels = $this->reward_service->getUserLevel($user,$campaign->id,$total_users,$campaign->max_user_ranking_size);
        $campaign_rewards = $this->reward_service->getCampaignReward($campaign->id);
        $departmentRankings = $this->reward_service->getDepartmentRankings($campaign->id);

        return view('admin.rewards.view', compact('campaign','users_with_levels','is_reward_given','campaign_rewards','departmentRankings'));
    }

    public function store($campaign, RewardRequest $request)
    {
        try {
            $campaign = $this->reward_service->getCompanyCampaigns($campaign);
            $user_ids = $request->input('user_id');
            $rewards = $request->input('reward');
            $reward =$this->reward_service->createBulkRewards($campaign, $user_ids, $rewards);
            if($reward){
                return $this->success(status: true, message: 'Reward has been given successfully!', code: 200);
            }
            return $this->error(status: false, message: 'Failed to give reward.', code: 500);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function customRewardsList(Request $request)
    {
        $type = $request->get('type', activeCampaignSeasonFilter());
        $companies = $this->reward_service->getCompanies();
        $campaigns = $this->reward_service->getCustomRewardsList($type, $request->company_id);
        return view('admin.rewards.custom-rewards.index', compact('companies', 'campaigns'));
    }

    public function customRewardsCreate()
    {
        $companies = $this->reward_service->getCompanies();
        return view('admin.rewards.custom-rewards.create', compact('companies'));
    }

    public function customRewardsStore(Request $request)
    {
        try {
            $request->validate([
                'company' => 'required_if:type,campaign',
                'campaign_season_id' => 'required|exists:campaigns_seasons,id',
                'custom_reward' => 'required|string|max:1000',
            ]);
            $reward = $this->reward_service->storeCustomRewards($request);
            if ($reward) {
                return $this->success(status: true, message: 'Custom rewards have been given successfully!', code: 200, result: [
                    'redirect_url' => route('admin.rewards.custom.index')
                ]);
            }
            return $this->error(status: false, message: 'Failed to give custom rewards.', code: 500);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getUnrewardedCampaigns($company_id)
    {
        try {
            $campaigns = $this->reward_service->getUnrewardedCampaigns($company_id);
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUnrewardedSeasons()
    {
        try {
            $seasons = $this->reward_service->getUnrewardedSeasons();
            return response()->json($seasons);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function customRewardsView($id)
    {
        $campaign_season = $this->reward_service->getCompanyCampaigns($id);
        return view('admin.rewards.custom-rewards.view', compact('campaign_season'));
    }

    public function customRewardsEdit($id)
    {
        $campaign_season = $this->reward_service->getCompanyCampaigns($id);
        $companies = $this->reward_service->getCompanies();
        $campaigns = $this->reward_service->getCompleteCompanyCampaigns($campaign_season->company_id);
        return view('admin.rewards.custom-rewards.edit', compact('campaign_season', 'companies', 'campaigns'));
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
