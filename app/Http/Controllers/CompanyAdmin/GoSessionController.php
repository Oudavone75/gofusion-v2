<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAdmin\SessionRequest;
use App\Models\CampaignsSeason;
use App\Services\GoSessionService;
use Illuminate\Support\Facades\Auth;
use App\Models\GoSession;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;

class GoSessionController extends Controller
{
    use AppCommonFunction, ApiResponse;
    private $user;
    public function __construct(public GoSessionService $go_session_service)
    {
        $this->user  = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $go_sessions = $this->go_session_service->getAllCompanySessions($this->user->company_id);
        return view('company_admin.sessions.index', compact('go_sessions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $campaigns = $this->go_session_service->getCompanyCampaigns($this->user->company_id);
        return view('company_admin.sessions.create', compact('campaigns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SessionRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->go_session_service->create($request->validated(), $this->user->company_id);
            DB::commit();

            // Notify Campaign Users
            $this->notifyCampaignUsers(
                campaign: CampaignsSeason::find($request->input('campaign')),
                notificationType: "Session_Added"
            );

            return redirect()->route('company_admin.sessions.index')->with('success', 'Session created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function showImportPage()
    {
        $campaigns = $this->go_session_service->getCompanyCampaigns($this->user->company_id);
        return view('company_admin.sessions.import', compact('campaigns'));
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
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
        if ($session->campaignSeason->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.sessions.index')->with('error', 'Session not found.');
        }
        return view('company_admin.sessions.view', compact('session'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoSession $session)
    {
        if ($session->campaignSeason->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.sessions.index')->with('error', 'Session not found.');
        }
        $campaigns = $this->go_session_service->getCompanyCampaigns($this->user->company_id);
        return view('company_admin.sessions.edit', compact('session', 'campaigns'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SessionRequest $request, GoSession $session)
    {
        try {
            if ($session->campaignSeason->company_id !== $this->user->company_id) {
                return redirect()->route('company_admin.sessions.index')->with('error', 'Session not found.');
            }
            $this->go_session_service->update(
                $session,
                $request->validated()
            );
            return redirect()->route('company_admin.sessions.index')->with('success', 'Updated successfully.');
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
        // $is_data_exist_of_this_session=$this->go_session_service->checkIfSessionHasStep($id);
        // if($is_data_exist_of_this_session){
        //     return response()->json([
        //         'data_exist_of_this_session' => true
        //     ]);
        // }
        $session = GoSession::find($id);
        if ($session->campaignSeason->company_id !== $this->user->company_id) {
            return redirect()->route('company_admin.sessions.index')->with('error', 'Session not found.');
        }
        try {
            DB::beginTransaction();
            if ($session->status === 'active' && $this->hasAnyAttempts($session)) {
                return response()->json([
                    'message' => 'Cannot delete an active session that has attempts'
                ]);
            }
            $this->go_session_service->delete($session);
            DB::commit();
            return redirect()->route('company_admin.sessions.index')->with('success', 'Deleted successfully.');
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
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function hasAnyAttempts($session)
    {
        $firstStep = $session->goSessionSteps->first();

        return $firstStep && $firstStep->quizStep && $firstStep->quizStep->attempts()->exists();
    }

}
