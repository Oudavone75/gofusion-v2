<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpinWheelStoreRequest;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Services\SpinWheelValidationStepService;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SpinWheelController extends Controller
{
    use AppCommonFunction, ApiResponse;
    public function __construct(
        private SpinWheelValidationStepService $spin_wheel_validation_step_service,
        private CampaignSeasonService $campaign_season_service,
        private GoSessionService $go_session_service
    ) {}

    public function list(Request $request)
    {
        $request_data = $request->all();
        $companies = $this->getAllCompanies();
        $spin_wheels = $this->spin_wheel_validation_step_service->getSpinWheelList($request_data, $request_data['company_id'] ?? null);
        return view('admin.spin.index', compact('spin_wheels', 'companies'));
    }

    public function create()
    {
        $companies = $this->getAllCompanies();
        return view('admin.spin.create', compact('companies'));
    }

    public function store(SpinWheelStoreRequest $request)
    {
        try {
            $response = $this->spin_wheel_validation_step_service->addSpinWheelStep($request->all());
            if ($response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 500);
            }
            return $this->success(status: true, message: $response['message'], result: [
                'url' => route('admin.spin.index'),
                'data' => $response['data']
            ]);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage());
        }
    }

    public function edit($id)
    {
        $spin_wheel = $this->spin_wheel_validation_step_service->getSpinWheelDetails(id: $id);
        $companies = $this->getAllCompanies();
        $company_id = $spin_wheel->goSessionStep->goSession->campaignSeason->company_id;
        $company_campaigns = $this->campaign_season_service->getCompanyCampaigns($company_id);
        $campaign_sessions = $this->go_session_service->getGoSessions($spin_wheel->goSessionStep->goSession->campaignSeason->id);
        return view('admin.spin.edit', compact('spin_wheel', 'companies', 'company_campaigns', 'campaign_sessions'));
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
                return $this->error(status: false, message: $response['message'], code: 500);
            }
            return $this->success(status: true, message: $response['message'], result: [
                'url' => route('admin.spin.index'),
                'data' => $response['data']
            ]);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage());
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
        return redirect()->route('admin.spin.index')
            ->with('success', 'Image Step deleted successfully!');
    }

    public function attemptedUsers(Request $request, $id, $type)
    {
        $spin_detail = $this->spin_wheel_validation_step_service->getSpinWheelDetails($id);
        $users = $this->spin_wheel_validation_step_service->getAttemptedUsers($spin_detail->go_session_step_id, $request->search);
        $go_session_step_id = $spin_detail->go_session_step_id;
        if ($request->ajax()) {
            return view('admin.spin.attempted-users', compact('users', 'spin_detail', 'go_session_step_id', 'type'))->render();
        }
        return view('admin.spin.attempted-users', compact('users', 'spin_detail', 'go_session_step_id', 'type'));
    }

    public function view($id)
    {
        $spin_wheel = $this->spin_wheel_validation_step_service->getSpinWheelDetails($id);
        return view('admin.spin.view', compact('spin_wheel'));
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
