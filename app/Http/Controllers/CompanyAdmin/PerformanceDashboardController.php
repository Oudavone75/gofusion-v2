<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Services\PerformanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceDashboardController extends Controller
{
    private $user;

    public function __construct(private PerformanceDashboardService $dashboardService)
    {
        $this->user = Auth::user();
    }

    /**
     * AJAX endpoint: returns dashboard stats JSON for a given campaign season.
     */
    public function dashboardStats(Request $request)
    {
        $request->validate([
            'campaign_season_id' => 'required|exists:campaigns_seasons,id',
            'page' => 'nullable|integer|min:1',
        ]);

        $stats = $this->dashboardService->getDashboardStats(
            $request->campaign_season_id,
            $this->user->company_id,
            (int) $request->input('page', 1)
        );

        return response()->json($stats);
    }

    /**
     * Employee detail page.
     */
    public function employeeDetail(int $userId, Request $request)
    {
        $request->validate([
            'campaign_season_id' => 'required|exists:campaigns_seasons,id',
            'quiz_page' => 'nullable|integer|min:1',
            'video_page' => 'nullable|integer|min:1',
        ]);

        $data = $this->dashboardService->getEmployeeDetail(
            $userId,
            $request->campaign_season_id,
            (int) $request->input('quiz_page', 1),
            (int) $request->input('video_page', 1)
        );

        if (!$data['user'] || $data['user']->company_id !== $this->user->company_id) {
            abort(404, 'Employee performance not found.');
        }

        $campaigns = $this->dashboardService->getAllCampaigns($this->user->company_id);

        return view('company_admin.performance.employee-detail', array_merge($data, [
            'campaigns' => $campaigns,
            'selectedCampaignId' => $request->campaign_season_id,
        ]));
    }
}
