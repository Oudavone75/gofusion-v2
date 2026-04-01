<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignCreateRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;
use App\Jobs\SendFirebaseNotification;
use App\Models\CampaignsSeason;
use App\Models\Company;
use App\Services\CampaignSeasonService;
use Illuminate\Http\Request;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    use AppCommonFunction;
    public function __construct(private CampaignSeasonService $campaign_service) {}
    public function index(Request $request)
    {
        $companies = $this->campaign_service->getCompanies();
        $campaigns = $this->campaign_service->getCampaigns($request->company_id);
        return view('admin.campaign.index', compact('campaigns', 'companies'));
    }

    public function view(CampaignsSeason $campaign)
    {
        $departmentRankings = DB::table('user_scores')
            ->join('company_departments as d', 'user_scores.company_department_id', '=', 'd.id')
            ->select(
                'user_scores.company_department_id',
                'd.name as department_name',
                DB::raw('SUM(user_scores.points) as total_points')
            )
            ->where('user_scores.campaign_season_id', $campaign->id)
            ->groupBy('user_scores.company_department_id', 'd.name')
            ->orderBy('total_points', 'desc')
            ->get();
        return view('admin.campaign.view', compact('campaign', 'departmentRankings'));
    }
    public function create()
    {
        $companies = $this->campaign_service->getCompanies();
        return view('admin.campaign.create', compact('companies'));
    }

    public function getByCompany(Company $company)
    {
        $departments = $company->departments;
        return response()->json($departments);
    }

    public function store(CampaignCreateRequest $request)
    {
        try {
            $type = $request->type;
            $typeName = $type === 'season' ? 'Season' : 'Campaign';
            if ($this->campaign_service->checkExistingCampaign($request->company_id, $request->start_date, $request->end_date, type: $request->type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A ' . $typeName . ' already exists for the selected date range.'
                ], 500);
            }
            $this->campaign_service->create(request: $request->validated(), is_admin: true);
            return response()->json([
                'success' => true,
                'message' => $typeName . ' created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(CampaignsSeason $campaign)
    {
        try {
            $companies = $this->campaign_service->getCompanies();
            $departments = $this->campaign_service->getCompanyDepartments($campaign->company_id);
            return view('admin.campaign.edit', compact('campaign', 'companies', 'departments'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update(UpdateCampaignRequest $request, CampaignsSeason $campaign)
    {
        $type = $request->type;
        $typeName = $type === 'season' ? 'Season' : 'Campaign';
        try {
            if ($this->campaign_service->checkExistingCampaign($request->company_id, $request->start_date, $request->end_date, $campaign->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A ' . $typeName . ' already exists for the selected date range.'
                ], 500);
            }
            $this->campaign_service->update($campaign, $request->validated(), true);
            return response()->json([
                'success' => true,
                'message' => $typeName . ' updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function delete(CampaignsSeason $campaign)
    {
        try {
            $is_data_exist_of_this_campaign = $this->campaign_service->checkIfCampaignHasSession($campaign->id);
            if ($is_data_exist_of_this_campaign) {
                return response()->json([
                    'data_exist_of_this_campaign' => true
                ]);
            }
            $this->campaign_service->delete($campaign);
            return redirect()->route('admin.campaign.index')
                ->with('success', 'Deleted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function changeStatus(CampaignsSeason $campaign)
    {
        try {
            $isSeason = is_null($campaign->company_id);
            if ($this->campaign_service->checkActiveCampaignExists($campaign->company_id)) {
                if (!$this->campaign_service->checkActiveCampaignPassed($campaign->company_id)) {
                    $this->campaign_service->makeItComplate($campaign->company_id);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can not make this ' . ($isSeason ? 'season' : 'campaign') . ' active because another ' . ($isSeason ? 'season' : 'campaign') . ' is already active.'
                    ], 500);
                }
            }
            $campaign->update([
                'status' => config('constants.STATUS.ACTIVE')
            ]);

            // Notify Campaign Users
            $this->notifyCampaignUsers(campaign: $campaign, notificationType: "Campaign_Activation");

            return response()->json([
                'success' => true,
                'message' => 'Status changed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getCompanyCampaigns($company_id)
    {
        try {
            $campaigns = $this->campaign_service->getCompanyCampaigns($company_id);
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getSeasons()
    {
        try {
            $seasons = $this->campaign_service->getSeasons();
            return response()->json($seasons);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
