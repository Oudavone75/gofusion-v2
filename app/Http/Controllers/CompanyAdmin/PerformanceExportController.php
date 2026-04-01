<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\CampaignsSeason;
use App\Services\PerformanceExportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PerformanceExportController extends Controller
{
    use ApiResponse;

    public function __construct(private PerformanceExportService $exportService) {}

    public function showExportPage()
    {
        $user = auth()->user();
        $campaigns = CampaignsSeason::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('company_admin.performance.export', compact('campaigns'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'campaign_season_id' => 'required|exists:campaigns_seasons,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $campaign = CampaignsSeason::findOrFail($request->campaign_season_id);

        if ($campaign->company_id !== $user->company_id) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to export this campaign.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to export this campaign.');
        }

        try {
            return $this->exportService->export(
                $request->campaign_season_id,
                $request->start_date,
                $request->end_date
            );
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
