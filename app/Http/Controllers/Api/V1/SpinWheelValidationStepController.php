<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\SpinWheelValidationStepService;
use App\Services\UserProgressService;
use App\Services\UserScoreService;
use App\Events\UserProgressEvent;
use App\Events\UserScoreEvent;
use App\Http\Requests\SpinWheelStepDetailsRequest;
use App\Http\Requests\SpinWheelStepCreateRequest;
use App\Models\SpinWheelSubmissionStep;
use Illuminate\Support\Facades\DB;
use App\Models\SpinWheel;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendFirebaseNotification;

class SpinWheelValidationStepController extends Controller
{
    use ApiResponse, AppCommonFunction;
    public function __construct(
        public SpinWheelValidationStepService $spin_wheel_validation_step_service,
        public UserProgressService $user_progress_service,
        public UserScoreService $user_score_service
    ) {}

    public function getSpinWheelStepDetails(SpinWheelStepDetailsRequest $request)
    {
        try {
            $spin_wheel_step_details = $this->spin_wheel_validation_step_service->getSpinWheelStepDetails($request->validated());
            if (empty($spin_wheel_step_details['data'])) {
                return $this->error(status: false, message: $spin_wheel_step_details['message']);
            }
            return $this->success(status: true, message: $spin_wheel_step_details['message'], result: $spin_wheel_step_details['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function createSpinWheelSubmissionStep(SpinWheelStepCreateRequest $request)
    {
        try {
            $go_session_step_id = $request->go_session_step_id;
            $spin_wheel_submission_step = SpinWheelSubmissionStep::where('go_session_step_id', $go_session_step_id)
                ->where('user_id', auth()->user()->id)
                ->first();
            if ($spin_wheel_submission_step) {
                return $this->error(status: false, message: trans('general.already_attempted'), code: 422);
            }
            DB::beginTransaction();

            $user = Auth::user();

            $spin_wheel = SpinWheel::with(['goSessionStep.goSession.campaignSeason'])->where('go_session_step_id', $go_session_step_id)->first();
            if (!$spin_wheel) {
                return $this->error(status: false, message: trans('general.step_not_found'));
            }
            $request->merge([
                'user_id' => $user->id,
                'spin_wheel_id' => $spin_wheel->id,
            ]);

            $already_attempted = SpinWheelSubmissionStep::where([
                'spin_wheel_id' => $spin_wheel->id,
                'go_session_step_id' => $go_session_step_id,
                'user_id' => $user->id,
            ])->exists();

            if ($already_attempted) {
                return $this->error(status: false, message: trans('general.already_attempted'), code: 409);
            }
            $request->merge([
                'points' => (int)$spin_wheel->points
            ]);
            SpinWheelSubmissionStep::create($request->all());
            $progress_payload = $this->getUserProgressPayload($go_session_step_id, $user, 1);
            if ($progress_payload) {
                event(new UserProgressEvent($progress_payload));
            }

            $score_payload = $this->getUserScorePayload($go_session_step_id, $user, $spin_wheel->points ?? 0);
            if ($score_payload) {
                event(new UserScoreEvent($score_payload));
            }

            if ($request->bonus_type === 'bonus_leaves') {
                $leaves_payload = $this->getSpinWheelLeavesPayload($go_session_step_id, $user, $spin_wheel->bonus_leaves);
                if ($leaves_payload) {
                    event(new UserScoreEvent($leaves_payload));
                    //Send Firebase Notification
                    $authUser = Auth::user();
                    try {
                        $locale = userLanguage(userId: $authUser->id);
                        SendFirebaseNotification::dispatch(
                            $authUser->id,
                            __('notifications.Leave_Added.title',locale: $locale),
                            __('notifications.Leave_Added.content', ['NumberOfLeaves' => $spin_wheel->bonus_leaves], locale: $locale),
                            'Leave_Added',['Type'=>'Leave_Added']
                        );
                    }catch (\Exception $exception){}
                }
            }
            $result = [
                'earned_points' => (int)$spin_wheel->points,
            ];
            if ($request->bonus_type === 'promo_codes') {
                $result['promo_codes'] = $spin_wheel->promo_codes;
            }
            if ($request->bonus_type === 'video_url') {
                $result['video_url'] = $spin_wheel->video_url;
            }
            if ($request->bonus_type === 'bonus_leaves') {
                $result['earned_leaves'] = $spin_wheel->bonus_leaves;
            }
            DB::commit();

            return $this->success(
                status: true,
                message: trans('general.spin_wheel_submission_step_created'),
                result: $result
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(status: false, message: $th->getMessage(), code: 500);
        }
    }
}
