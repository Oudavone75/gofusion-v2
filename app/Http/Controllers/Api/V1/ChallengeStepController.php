<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StoreCompleteUserSessionEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Events\UserProgressEvent;
use App\Services\ChallengeStepService;
use App\Http\Requests\CreateChallengeRequest;
use App\Http\Requests\UploadChallengeImageRequest;
use App\Http\Requests\ValidateChallengeRequest;
use App\Models\ChallengeStep;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use App\Events\UserScoreEvent;
use App\Http\Requests\CreateInspirationChallengeRequest;
use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Services\ChallengeService;
use Illuminate\Support\Facades\Auth;

class ChallengeStepController extends Controller
{
    use ApiResponse, AppCommonFunction;
    public function __construct(public ChallengeStepService $challenge_step_service, public ChallengeService $challenge_service) {}

    public function getThemes()
    {
        try {
            $challengeThemes = $this->challenge_step_service->getThemes();
            if ($challengeThemes['success'] == false) {
                return $this->error(status: false, message: $challengeThemes['message']);
            }
            return $this->success(status: true, message: $challengeThemes['message'], result: $challengeThemes['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getCategories()
    {
        try {
            $challengeCategories = $this->challenge_step_service->getCategories();
            if ($challengeCategories['success'] == false) {
                return $this->error(status: false, message: $challengeCategories['message']);
            }
            return $this->success(status: true, message: $challengeCategories['message'], result: $challengeCategories['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function createChallengeStep(CreateChallengeRequest $request)
    {
        try {
            $challenge_step = ChallengeStep::where('go_session_step_id', $request->go_session_step_id)
                ->where('user_id', auth()->user()->id)
                ->first();
            if ($challenge_step) {
                return $this->error(status: false, message: trans('general.already_attempted'), code: 422);
            }
            DB::beginTransaction();
            $user = Auth::user();
            $challenge = Challenge::with(['goSessionStep.goSession.campaignSeason'])->where([
                'go_session_step_id' => $request->go_session_step_id,
                'status' => 'active',
            ])->first();
            if (!$challenge) {
                return $this->error(status: false, message: trans('general.step_not_found'));
            }
            $already_attempted = ChallengeStep::where([
                'go_session_step_id' => $request->go_session_step_id,
                'user_id' => $user->id,
            ])->exists();
            if ($already_attempted) {
                return $this->error(status: false, message: trans('general.already_attempted'), code: 409);
            }
            $image_path = null; // default
            if ($request->hasFile('image')) {
                $filename = uploadFile($request->file('image'), 'public', ChallengeStep::IMAGE_PATH);
                $image_path = ChallengeStep::IMAGE_PATH . '/' . $filename;
                if (!$image_path) {
                    return ['success' => false, 'message' => trans('general.image_not_uploaded'), 'data' => []];
                }
            }
            $go_session_details = $this->getGoSessionDetails($request->go_session_step_id);
            $request->merge([
                'user_id' => $user->id,
                'campaign_id' => $go_session_details['campaign_season_id'],
                'company_id' => $user->company_id,
                'department_id' => $user->company_department_id,
                'points' => (int)$challenge->points,
                'image_path' => $image_path,
                'description' => $request->description,
                'video_url' => $request->video_url ?? null
            ]);
            $challenge_step = ChallengeStep::create($request->all());
            $category = ChallengeCategory::find($request->challenge_category_id);
            if ($challenge_step && $category->name == "Event") {
                $data = [...$request->all(),'event_name' => $request['title']];
                $event_data = $this->preparedEventData(validated_data: $data,format: 'event');
                $this->challenge_service->storeEventRelatedData(challenge_step: $challenge_step,  data: $event_data);
            }
            $progress_payload = $this->getUserProgressPayload($request->go_session_step_id, $user, 1);
            if ($progress_payload) {
                event(new UserProgressEvent($progress_payload));
                event(new StoreCompleteUserSessionEvent($progress_payload));
            }
            $score_payload = $this->getUserScorePayload($request->go_session_step_id, $user, $challenge_step->points ?? 0);
            if ($score_payload) {
                event(new UserScoreEvent($score_payload));
            }
            DB::commit();
            return $this->success(
                status: true,
                message: trans('general.challenge_created'),
                result: [
                    'earned_points' => (int)$challenge_step->points
                ]
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getThemeChallengesListing(Request $request)
    {
        try {
            $user = Auth::user();
            $mode = $user->isEmployee() ? 'employee' : 'citizen';
            $theme_id = $request->query('theme_id');
            $theme_challenges = $this->challenge_step_service->getThemeChallengesListing($mode, $theme_id);
            return $this->success(status: true, message: $theme_challenges['message'], result: $theme_challenges['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getThemeChallengeDetail($challenge_step_id)
    {
        try {
            $challenge_detail = $this->challenge_step_service->getThemeChallengeDetail($challenge_step_id);
            return $this->success(status: true, message: $challenge_detail['message'], result: $challenge_detail['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function uploadChallengeImage(UploadChallengeImageRequest $request)
    {
        try {
            $challenge_image = $this->challenge_step_service->uploadChallengeImage($request);
            return $this->success(status: true, message: $challenge_image['message'], result: $challenge_image['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function validateChallenge(ValidateChallengeRequest $request)
    {
        try {
            $response = $this->challenge_step_service->validateChallenge($request);
            if ($response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            return $this->success(status: true, message: $response['message'], result: $response['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function createInspirationChallenge(CreateInspirationChallengeRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $image_path = null; // default
            if ($request->hasFile('image')) {
                $filename = uploadFile($request->file('image'), 'public', ChallengeStep::IMAGE_PATH);
                if (!$filename) {
                    return [
                        'success' => false,
                        'message' => trans('general.image_not_uploaded'),
                        'data'    => []
                    ];
                }
                $image_path = ChallengeStep::IMAGE_PATH . '/' . $filename;
            }
            $request->merge([
                'user_id' => $user->id,
                'company_id' => $user->isEmployee() ? $user->company_id : null,
                'department_id' => $user->company_department_id ?? null,
                'points' => null,
                'image_path' => $image_path,
                'description' => $request->description,
                'video_url' => $request->video_url ?? null
            ]);
            $challenge_step = ChallengeStep::create($request->all());
            $category = ChallengeCategory::find($request->challenge_category_id);
            if ($challenge_step && $category->name == "Event") {
                $data = [...$request->all(),'event_name' => $request['title']];
                $event_data = $this->preparedEventData(validated_data: $data,format: 'event');
                $this->challenge_service->storeEventRelatedData(challenge_step: $challenge_step,  data: $event_data);
            }
            DB::commit();
            return $this->success(
                status: true,
                message: trans('general.challenge_created'),
                result: true
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}
