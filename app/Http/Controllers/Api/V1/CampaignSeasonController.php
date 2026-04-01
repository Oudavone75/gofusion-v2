<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CampaignSeasonService;
use App\Services\UserScoreService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CampaignSeasonController extends Controller
{
    use ApiResponse;

    public function __construct(private CampaignSeasonService $campaign_season_service, private UserScoreService $user_score_service) {}

    public function getActiveCampaignOrSeason(Request $request)
    {
        try {
            $user = Auth::user();
            $mode = $user->isEmployee() ? 'employee' : 'citizen';
            $response = $this->campaign_season_service->getActiveCampaingSeason($mode, $user);
            if (isset($response['status']) && $response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            return $this->success(true, 'get active campaign or season', $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getLeaderBoard(Request $request)
    {
        try {
            $request->validate([
                'type' => 'nullable|in:personal,department',
                'campaing_season_id' => 'nullable|exists:campaigns_seasons,id'
            ]);
            $user = Auth::user();
            $campaign_season = null;
            $company_id = $user->company_id;
            $company_department_id = $user->company_department_id;
            $campaign_season_id = $request->campaing_season_id;
            $type = $request->type ?? 'personal';
            $campaign_season = $this->campaign_season_service->getCampignOrSeasonsById($campaign_season_id);
            if (!$campaign_season && $campaign_season_id == null) {
                $campaign_season = $this->campaign_season_service->getAllActiveAndCompletedSeasons()->toArray();
                if (empty($campaign_season)) {
                    return $this->error(false, trans('general.campaign_season'));
                }
                $levels = config('constants.LEVELS');
                $isArray = is_array($campaign_season);
                $users_with_levels = $this->user_score_service->getUsersWithLevels($levels, $campaign_season, null, $isArray);
                return $this->success(true, 'Get leader board of the campaign or season.', $users_with_levels);
            }
            if (!$campaign_season) {
                return $this->error(false, trans('general.campaign_season'));
            }
            $levels = config('constants.LEVELS');
            $company_department_id = $type == 'personal' ? null : $company_department_id;
            if ($user->isCitizen()) {
                $company_department_id = null;
            }

            $users_with_levels = $this->user_score_service->getUsersWithLevels($levels, $campaign_season, $company_department_id);
            return $this->success(true, 'Get leader board of the campaign or season.', $users_with_levels);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage(), code: 400);
        }
    }
}
