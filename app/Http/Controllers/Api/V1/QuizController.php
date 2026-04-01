<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StoreCompleteUserSessionEvent;
use App\Events\UserProgressEvent;
use App\Events\UserScoreEvent;
use App\Http\Controllers\Controller;
use App\Services\QuizService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    use ApiResponse;

    public function __construct(private QuizService $quiz_service)
    {
        $this->quiz_service = $quiz_service;
    }

    public function getQuizStep($go_session_step_id)
    {
        try {
            $response = $this->quiz_service->getQuizWithQuestionAndOptions($go_session_step_id);
            if (isset($response['status']) && $response['status'] === false) {
                return $this->error(status: false, message: $response['message']);
            }
            return $this->success(true, 'Get quiz.', $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function attemptQuizStep($go_session_step_id, Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'points' => 'required'
            ]);
            $request_data = $request->all();
            $request_data['user_id'] = Auth::id();
            DB::beginTransaction();
            $response = $this->quiz_service->validateQuizAttempt($go_session_step_id, $request_data);
            if (isset($response['status']) && $response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            $this->quiz_service->storeQuizResponses($go_session_step_id, $request_data);
            event(new UserProgressEvent([
                'campaigns_season_id' => $response['campaigns_season_id'],
                'go_session_id' => $response['go_session_id'],
                'go_session_step_id' => $response['go_session_step_id'],
                'user_id' => $response['user']->id,
                'is_complete' => $response['is_complete']
            ]));
            
            $earned_points = $response['earned_points'] ?? $request_data['points'];
            
            event(new UserScoreEvent([
                'campaigns_season_id' => $response['campaigns_season_id'],
                'company_id' => $response['user']->company_id,
                'company_department_id' => $response['user']->company_department_id,
                'user_id' => $response['user']->id,
                'points' => $earned_points
            ]));
            event(new StoreCompleteUserSessionEvent([
                'campaigns_season_id' => $response['campaigns_season_id'],
                'user_id' => $response['user']->id,
                'go_session_id' => $response['go_session_id']
            ]));
            DB::commit();
            return $this->success(
                status : true,
                message: trans('general.quiz_challenge_completed'),
                result: [
                    'earned_points' => (int)$earned_points,
                ]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(false, $e->getMessage());
        }
    }
}
