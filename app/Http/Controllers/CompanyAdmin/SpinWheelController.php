<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\AppCommonFunction;
use App\Traits\ApiResponse;
use App\Services\SpinWheelValidationStepService;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Http\Requests\Admin\SpinWheelStoreRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SpinWheelController extends Controller
{
    use AppCommonFunction, ApiResponse;
    protected $views_directory = 'company_admin.session-steps.spin-wheel.';
    protected $route_directory = 'company_admin.steps.spin-wheel.';
    public function __construct(
        private SpinWheelValidationStepService $spin_wheel_validation_step_service,
        private CampaignSeasonService $campaign_season_service,
        private GoSessionService $go_session_service
    ) {}

    public function list(Request $request)
    {
        $request_data = $request->all();
        $company_id = Auth::user()->company_id;
        $spin_wheels = $this->spin_wheel_validation_step_service->getSpinWheelList($request_data, $company_id);
        return view($this->views_directory . 'index', compact('spin_wheels'));
    }

    public function create()
    {
        $company_id = Auth::user()->company_id;
        $campaigns = $this->getCompanyCampaigns($company_id);

        return view($this->views_directory . 'create', compact('campaigns'));
    }

    public function store(SpinWheelStoreRequest $request)
    {
        try {
            $request->merge(['company' => Auth::user()->company_id]);
            $response = $this->spin_wheel_validation_step_service->addSpinWheelStep($request->all());
            if ($response['status'] === false) {
                return response()->json([
                    'message' => $response['message']
                ], 500);
            }
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => $response['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $spin_wheel = $this->spin_wheel_validation_step_service->getSpinWheelDetails(id: $id);
        $companies = $this->getAllCompanies();
        $company_id = $spin_wheel->goSessionStep->goSession->campaignSeason->company_id;
        $company_campaigns = $this->campaign_season_service->getCompanyCampaigns($company_id);
        $campaign_sessions = $this->go_session_service->getGoSessions($spin_wheel->goSessionStep->goSession->campaignSeason->id);
        return view($this->views_directory . 'edit', compact('spin_wheel', 'companies', 'company_campaigns', 'campaign_sessions'));
    }

    public function update($id, SpinWheelStoreRequest $request)
    {
        try {
            $is_spin_wheel_exist = $this->spin_wheel_validation_step_service->isSpinWheelAttempted($id);
            if ($is_spin_wheel_exist) {
                return $this->error(status: false, message: 'Some users already attempted this step so you cannot edit this step.');
            }
            $update_data = $request->all();
            $response = $this->spin_wheel_validation_step_service->updateSpinWheel(id: $id, request: $update_data);
            if ($response['status'] === false) {
                return response()->json([
                    'message' => $response['message']
                ], 500);
            }
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => $response['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function delete($id)
    {
        try {
            $response = $this->spin_wheel_validation_step_service->deleteSpinWheelStep($id);
            if ($response['status'] === false) {
                return response()->json([
                    'record_exist' => true
                ]);
            }
        } catch (\Throwable $th) {
            return back()->withInput()
                ->with('error', $th->getMessage());
        }
        return redirect()->route($this->route_directory . 'index')
            ->with('success', 'Spin Wheel Step deleted successfully!');
    }

    public function attemptedUsers($id)
    {
        $spin_detail = $this->spin_wheel_validation_step_service->getSpinWheelDetails($id);
        $users = $this->spin_wheel_validation_step_service->getAttemptedUsers($spin_detail->go_session_step_id);
        $go_session_step_id = $spin_detail->go_session_step_id;
        return view('company_admin.session-steps.spin-wheel.attempted-users', compact('users', 'spin_detail', 'go_session_step_id'));
    }

    public function view($id)
    {
        $spin_wheel = $this->spin_wheel_validation_step_service->getSpinWheelDetails($id);
        return view('company_admin.session-steps.spin-wheel.view', compact('spin_wheel'));
    }

    public function export(Request $request, $id)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
            ]);

            if ($request->start_date && $request->end_date == null) {
                $request->end_date = $request->start_date;
            }

            $start_date = $request->start_date;
            $end_date   = $request->end_date;

            $extension = $request->type === 'csv' ? 'csv' : 'xlsx';

            if ($start_date && $end_date) {
                $file_name = 'spin_wheel_users_' .
                    Carbon::parse($start_date)->format('Y-m-d') .
                    '_to_' .
                    Carbon::parse($end_date)->format('Y-m-d') .
                    '.' . $extension;
            } else {
                $file_name = 'spin_wheel_users_all.' . $extension;
            }

            return $this->spin_wheel_validation_step_service->export(
                $start_date,
                $end_date,
                $request->reward_type,
                $file_name,
                $id,
                $request->type
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
