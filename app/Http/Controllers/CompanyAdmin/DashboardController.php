<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\CampaignsSeason;
use App\Models\Company;
use App\Services\Admin\CompanyService;
use App\Services\CampaignSeasonService;
use App\Services\ChallengeService;
use App\Services\GoSessionService;
use App\Services\PerformanceDashboardService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $user;
    public function __construct(private CampaignSeasonService $campaign_service,private CompanyService $company_service, private GoSessionService $go_session_service, private ChallengeService $challenge_service, private PerformanceDashboardService $performance_dashboard_service) {
        $this->user  = Auth::user();
    }

    public function index()
    {
        $campaignCount =  $this->campaign_service->getCampaignsCount($this->user->company_id);
        $departmentCount = $this->campaign_service->getCompanyDepartments($this->user->company_id)->count();
        $employeeCount = $this->company_service->getEmployeesCount($this->user->company_id);
        $inspirationalChallengeCount = $this->challenge_service->getChallengesCount($this->user->company_id);
        $challenges = $this->challenge_service->getPendingChallengesDasboard($this->user->company_id);
        $go_sessions = $this->go_session_service->getCompanySessionsDashboard($this->user->company_id);

        // Performance dashboard data
        $allCampaigns = $this->performance_dashboard_service->getAllCampaigns($this->user->company_id);
        $activeCampaign = $this->performance_dashboard_service->getActiveCampaign($this->user->company_id);
        $activeCampaignId = $activeCampaign?->id ?? $allCampaigns->first()?->id;

        return view('company_admin.dashboard', compact(
            'campaignCount',
            'departmentCount',
            'employeeCount',
            'inspirationalChallengeCount',
            'challenges',
            'go_sessions',
            'allCampaigns',
            'activeCampaignId'
        ));
    }
}
