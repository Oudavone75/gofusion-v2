<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GoSessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoSessionController extends Controller
{
    use ApiResponse;

    public function __construct(private GoSessionService $go_session_service)
    {
        $this->go_session_service = $go_session_service;
    }

    public function getGoSessions($campaign_season_id)
    {
        try {
            $response = $this->go_session_service->getGoSessions($campaign_season_id);
            return $this->success(true, trans('general.sessions_fetched'), $response);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage());
        }
    }

    public function getSessionStepsList($go_session_id)
    {
        try {
            $response = $this->go_session_service->fetchSessionSteps($go_session_id);
            return $this->success(true, trans('general.session_steps_fetched'), $response);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage());
        }
    }

    public function getGoSessionDetails($id)
    {
        try {
            $response = $this->go_session_service->getGoSessionDetails($id);
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message']);
            }
            return $this->success(true, trans('general.session_details_fetched'), $response);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage(), code: 400);
        }
    }

    public function getSessionProgress($campaign_season_id)
    {
        try {
            $response = $this->go_session_service->trackWeeklyProgress($campaign_season_id);
            return $this->success(true, trans('general.session_progress_fetched'), $response);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage());
        }
    }

    public function updateSessionTimeDuration(Request $request)
    {
        try {
            $request->validate([
                'session_time_duration_id' => 'required|exists:session_time_durations,id',
            ]);

            $user = Auth::user();
            $response = $this->go_session_service->updateSessionTimeDuration($request->all(), $user);

            if (!$response['success']) {
                return $this->error(false, $response['message']);
            }

            return $this->success(true, $response['message'], $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getSessionTimeDurations()
    {
        try {
            $response = $this->go_session_service->getAllSessionTimeDurations();

            if (!$response['success']) {
                return $this->error(false, $response['message']);
            }

            return $this->success(true, $response['message'], $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }
}
