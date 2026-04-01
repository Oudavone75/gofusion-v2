<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAdmin\CampaignRequest;
use App\Services\CampaignSeasonService;
use Illuminate\Support\Facades\Auth;
use App\Models\CampaignsSeason;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;

class CampaignSeasonController extends Controller
{
    use AppCommonFunction;
    private $user;
    public function __construct(public CampaignSeasonService $campaign_season_service)
    {
        $this->user  = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campaign_seasons = $this->campaign_season_service->getCampaigns($this->user->company_id);

        return view('company_admin.campaigns.index', compact('campaign_seasons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = $this->campaign_season_service->getCompanyDepartments($this->user->company_id);
        return view('company_admin.campaigns.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignRequest $request)
    {
        try {
            if ($this->campaign_season_service->checkExistingCampaign($this->user->company_id, $request->start_date, $request->end_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign already exists for the selected date range.'
                ], 500);
            }
            $this->campaign_season_service->create($request->validated(), $this->user->company_id);
            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CampaignsSeason $campaign)
    {
        if ($campaign->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.campaigns.index')->with('error', 'Campaign not found.');
        }
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
        return view('company_admin.campaigns.view', compact('campaign', 'departmentRankings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CampaignsSeason $campaign)
    {
        if ($campaign->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.campaigns.index')->with('error', 'Campaign not found.');
        }
        $departments = $this->campaign_season_service->getCompanyDepartments($this->user->company_id);
        return view('company_admin.campaigns.edit', compact('campaign', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampaignRequest $request, CampaignsSeason $campaign)
    {
        try {
            if ($campaign->company_id !== $this->user->company_id) {
                return redirect()->route('company_admin.campaigns.index')->with('error', 'Campaign not found.');
            }
            if ($this->campaign_season_service->checkExistingCampaign($this->user->company_id, $request->start_date, $request->end_date, $campaign->id)) {
                return back()->withInput()
                    ->with('error', 'Campaign already exists for the selected date range.');
            }
            $this->campaign_season_service->update($campaign, $request->validated());
            return redirect()->route('company_admin.campaigns.index')->with('success', 'Updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $is_data_exist_of_this_campaign = $this->campaign_season_service->checkIfCampaignHasSession($id);
        if ($is_data_exist_of_this_campaign) {
            return response()->json([
                'data_exist_of_this_campaign' => true
            ]);
        }
        $campaign = CampaignsSeason::find($id);
        if ($campaign->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.campaigns.index')->with('error', 'Campaign not found.');
        }
        $this->campaign_season_service->delete($campaign);

        return redirect()->route('company_admin.campaigns.index')->with('success', 'Deleted successfully.');
    }

    public function changeStatus($id)
    {
        if (!request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request.'
            ], 500);
        }
        try {
            $campaign = CampaignsSeason::find($id);
            if ($campaign->company_id !== $this->user->company_id) {
                return redirect()->route('company_admin.campaigns.index')->with('error', 'Campaign not found.');
            }
            if ($this->campaign_season_service->checkActiveCampaignExists($this->user->company_id)) {
                if (!$this->campaign_season_service->checkActiveCampaignPassed($this->user->company_id)) {
                    $this->campaign_season_service->makeItComplate($this->user->company_id);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Campaign is already active.'
                    ], 500);
                }
            }
            $campaign->update([
                'status' => 'active'
            ]);

            // Notify Campaign Users
            $this->notifyCampaignUsers(campaign: $campaign, notificationType: "Campaign_Activation");

            return response()->json([
                'success' => true,
                'message' => 'Campaign status changed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
