<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignsSeason;
use App\Models\Company;
use App\Models\UserCarbonFootprint;
use App\Services\CampaignSeasonService;
use App\Services\CarbonFootprintService;
use App\Services\ChallengeService;
use App\Services\PerformanceDashboardService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private CampaignSeasonService $campaign_service,
        private UserService $user_service,
        private ChallengeService $challenge_service,
        private CarbonFootprintService $carbon_footprint_service,
        private PerformanceDashboardService $performance_dashboard_service
    ) {}

    public function index()
    {
        $campaignCount =  $this->campaign_service->getCampaignsCount();
        $companyCount = $this->campaign_service->getCompanies()->count();
        $citizenCount = $this->user_service->getCitizensCount();
        $inspirationalChallengeCount = $this->challenge_service->getChallengesCount();
        $challenges = $this->challenge_service->getPendingChallengesDasboard();
        $campaigns = CampaignsSeason::whereNull('company_id')
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Performance dashboard data — default type is 'campaign' (has company_id)
        $allCampaigns = $this->performance_dashboard_service->getAllCampaigns(null, 'campaign');
        $allSeasons = $this->performance_dashboard_service->getAllCampaigns(null, 'season');
        $activeCampaignId = $allCampaigns->first()?->id;

        // Prepare JSON-safe arrays for JS
        $campaignsJson = $allCampaigns->map(function ($c) {
            return ['id' => $c->id, 'title' => $c->title, 'start_date' => $c->start_date, 'end_date' => $c->end_date];
        })->values();
        $seasonsJson = $allSeasons->map(function ($c) {
            return ['id' => $c->id, 'title' => $c->title, 'start_date' => $c->start_date, 'end_date' => $c->end_date];
        })->values();

        return view('admin.dashboard', compact(
            'campaignCount',
            'companyCount',
            'citizenCount',
            'inspirationalChallengeCount',
            'challenges',
            'campaigns',
            'allCampaigns',
            'allSeasons',
            'activeCampaignId',
            'campaignsJson',
            'seasonsJson'
        ));
    }

    public function carbonAssessment(Request $request)
    {
        $search = $request->search;
        $query = UserCarbonFootprint::with(['user'])->orderBy('created_at', 'desc');
        if ($search) {
            $query->where('water_value', 'like', "%$search%")->orWhere('carbon_value', 'like', "%$search%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
        }
        $carbonFootPrintAssessments = $query->paginate(10);
        if ($request->ajax()) {
            return view('admin.carbon-assessment.index', compact('carbonFootPrintAssessments'))->render();
        }
        return view('admin.carbon-assessment.index', compact('carbonFootPrintAssessments'));
    }

    /**
     * Export carbon assessments with date filters (AJAX)
     */
    public function exportCarbonAssessments(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:xlsx,csv',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $format = $request->format;
            $fileName = 'carbon_assessments_' . date('Y-m-d_His') . '.' . $format;

            // Build query with filters
            $query = UserCarbonFootprint::with(['user']);
            $start_date = $request->start_date;
            $end_date   = $request->end_date ?? $start_date;

            if ($start_date && $end_date) {
                $query->whereBetween('attempt_at', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay(),
                ]);
            }

            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where('water_value', 'like', "%$search%")->orWhere('carbon_value', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
            }

            $query->orderBy('attempt_at', 'desc');

            // Route to appropriate export method
            if ($format === 'csv') {
                return $this->carbon_footprint_service->exportAsCSV($query, $fileName);
            } else {
                return $this->carbon_footprint_service->exportAsExcel($query, $fileName);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of records for export preview
     */
    public function getExportCount(Request $request)
    {
        $query = UserCarbonFootprint::query();

        if ($request->start_date) {
            $query->whereDate('attempt_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('attempt_at', '<=', $request->end_date);
        }

        $count = $query->count();

        return response()->json(['count' => $count]);
    }
}
