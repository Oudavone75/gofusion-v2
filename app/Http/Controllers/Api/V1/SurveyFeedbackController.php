<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StoreCompleteUserSessionEvent;
use App\Events\UserProgressEvent;
use App\Events\UserScoreEvent;
use App\Http\Controllers\Controller;
use App\Services\SurveyFeedbackService;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyFeedbackController extends Controller
{
    use ApiResponse, AppCommonFunction;

    public function __construct(private SurveyFeedbackService $survey_feedback_service)
    {
        $this->survey_feedback_service = $survey_feedback_service;
    }

    public function getSurveyFeedback($go_session_step_id)
    {
        try {
            $response = $this->survey_feedback_service->getFeedbackByStepId($go_session_step_id);
            if (isset($response['status']) && $response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            return $this->success(true, trans('general.survey_fetched'), $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function submitSurveyStep($go_session_step_id, Request $request)
    {
        try {
            $request->validate([
                'survey_feedback_id' => 'required|exists:survey_feedback,id'
            ]);
            $request_data = $request->all();
            $user = Auth::user()->load(['userDetails.sessionTimeDuration']);
            $request_data['user_id'] = $user->id;
            $request_data['go_session_step_id'] = $go_session_step_id;
            DB::beginTransaction();
            // $survey_feedback = $this->survey_feedback_service->getSurveyFeedbackWithRelations($request_data['survey_feedback_id']);
            // $campaign_season_id = $survey_feedback->campaign_season_id;
            // $checkUserWeeklySessionCount = $this->checkUserWeeklySessionCount($user, $user->userDetails->sessionTimeDuration, $campaign_season_id);
            // if ($checkUserWeeklySessionCount === false) {
            //     return $this->error(status: false, message: trans('general.weekly_session_limit'), code: 400);
            // }
            $response = $this->survey_feedback_service->createSurveyStepAttempt($request_data);
            if (isset($response['status']) && $response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            $progress_payload = $this->getUserProgressPayload($go_session_step_id, $user, 1);
            if ($progress_payload) {
                event(new UserProgressEvent($progress_payload));
            }

            $score_payload = $this->getUserScorePayload($go_session_step_id, $user, $response['survey_feedback']->points ?? 0);
            if ($score_payload) {
                event(new UserScoreEvent($score_payload));
            }

            $leaves_payload = $this->getUserScoreLeavesPayload($go_session_step_id, $user, 1);
            if ($leaves_payload) {
                event(new UserScoreEvent($leaves_payload));
            }
            event(new StoreCompleteUserSessionEvent($progress_payload));
            DB::commit();
            return $this->success(
                status: true,
                message: trans('general.survey_step_submitted'),
                result: [
                    'earned_points' => (int)$response['survey_feedback']->points,
                ]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(false, $e->getMessage());
        }
    }
}
