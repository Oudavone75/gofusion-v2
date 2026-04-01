<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SessionRequest;
use App\Models\CampaignsSeason;
use App\Services\GoSessionService;
use App\Models\GoSession;
use Illuminate\Support\Facades\DB;
use App\Services\CampaignSeasonService;
use Illuminate\Http\Request;
use App\Traits\AppCommonFunction;
use App\Traits\ApiResponse;

class GoSessionController extends Controller
{
    use AppCommonFunction, ApiResponse;
    public function __construct(private GoSessionService $go_session_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = $this->go_session_service->getCompanies();
        $go_sessions = $this->go_session_service->getAllCompanySessions($request->company_id);
        return view('admin.sessions.index', compact('go_sessions', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = $this->go_session_service->getCompanies();
        return view('admin.sessions.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:campaign,season',
                'company' => 'required_if:type,campaign|exists:companies,id',
                'campaign' => 'required|exists:campaigns_seasons,id',
                'title' => 'required|string|max:255',
            ]);
            DB::beginTransaction();
            $this->go_session_service->create($request);
            DB::commit();

            // Notify Campaign Users
            $this->notifyCampaignUsers(
                campaign: CampaignsSeason::find($request->input('campaign')),
                notificationType: "Session_Added"
            );

            return response()->json([
                'success' => true,
                'message' => 'Session created successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showImportPage()
    {
        $companies = $this->go_session_service->getCompanies();
        return view('admin.sessions.import', compact('companies'));
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:campaign,season',
                'company' => 'required_if:type,campaign|exists:companies,id',
                'campaign' => 'required|exists:campaigns_seasons,id',
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $response = $this->go_session_service->importSessions($request);
            if (isset($response['success']) && $response['success'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'],
                    'result' => $response['errors'] ?? []
                ], 500);
            }

            return $this->success(message: 'Sessions imported successfully!',result: []);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GoSession $session)
    {
        return view('admin.sessions.view', compact('session'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoSession $session, CampaignSeasonService $campaign_season_service)
    {
        $companies = $this->go_session_service->getCompanies();
        $company_id = $session->campaignSeason->company_id;
        $company_campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        return view('admin.sessions.edit', compact('session', 'companies', 'company_campaigns'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SessionRequest $request, GoSession $session)
    {
        try {
            $this->go_session_service->update(
                $session,
                $request->validated()
            );
            return response()->json([
                'success' => true,
                'message' => 'Session updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $session = GoSession::find($id);
        try {
            DB::beginTransaction();
            $this->go_session_service->delete($session);
            DB::commit();
            return redirect()->route('admin.sessions.index')->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function getCampaignSessions($campaign_id)
    {
        try {
            $sessions = $this->go_session_service->getGoSessions($campaign_id);
            return response()->json($sessions);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() . '=>' . $e->getLine() . ' in ' . $e->getFile()
            ], 500);
        }
    }
}
