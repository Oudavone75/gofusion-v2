<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendFirebaseNotification;
use App\Services\ChallengeStepService;
use App\Http\Requests\Admin\StoreChallenegeStepRequest;
use App\Http\Requests\Admin\ChangeChallengeStatusRequest;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ChallengeStepController extends Controller
{
    use ApiResponse;
    public function __construct(private ChallengeStepService $challenge_step_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $challenge_steps = $this->challenge_step_service->getAllChallengesSteps($request->company_id);
        $companies = $this->challenge_step_service->getCompanies();
        return view('admin.challenge-step.index', compact('challenge_steps', 'companies'));
    }

    public function create()
    {
        $companies = $this->challenge_step_service->getCompanies();
        return view('admin.challenge-step.create', compact('companies'));
    }

    public function store(StoreChallenegeStepRequest $request)
    {
        try {
            $go_session_step_id = $this->challenge_step_service->getGoSessionStepId($request->session);
            $is_already_exist = $this->challenge_step_service->isChallengeStepGuideLineExists($go_session_step_id);
            if ($is_already_exist) {
                return $this->error(status: false, message: 'Challenge Step already exists for this session.', code: 500);
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            $validated_data['company_id'] = $request['company'];
            $this->challenge_step_service->create($validated_data);
            return $this->success(status: true, message: 'Challenge Step created successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function show($id)
    {
        $challenge_step = $this->challenge_step_service->getChallengeStepDetails($id);
        return view('admin.challenge-step.view', compact('challenge_step'));
    }

    public function edit($id, CampaignSeasonService $campaign_season_service, GoSessionService $go_session_service)
    {
        $challenge_step = $this->challenge_step_service->getChallengeStepDetails($id);
        $companies = $this->challenge_step_service->getCompanies();
        $company_id = $challenge_step->goSessionStep->goSession->campaignSeason->company_id;
        $company_campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        $company_campaigns_id = $challenge_step->goSessionStep->goSession->campaignSeason->id;
        $campaign_sessions = $go_session_service->getGoSessions($company_campaigns_id);
        return view('admin.challenge-step.edit', compact('challenge_step', 'companies', 'company_campaigns', 'campaign_sessions'));
    }

    public function update(StoreChallenegeStepRequest $request, $id)
    {
        try {
            $go_session_step_id = $this->challenge_step_service->getGoSessionStepId($request->session);
            $is_already_exist = $this->challenge_step_service->isChallengeStepGuideLineExists($go_session_step_id, $id);
            if ($is_already_exist) {
                return $this->error(status: false, message: 'Challenge Step already exists for this session.', code: 500);
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            $challenge_step = $this->challenge_step_service->getChallengeStepDetails($id);
            $this->challenge_step_service->update($challenge_step, $validated_data);
            return $this->success(status: true, message: 'Challenge Step updated successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $challenge_step_details = $this->challenge_step_service->getChallengeStepDetails($id);
            $is_data_exist_of_this_image_step = $this->challenge_step_service->isChallengeStepExists($challenge_step_details->go_session_step_id);
            if ($is_data_exist_of_this_image_step) {
                return response()->json([
                    'record_exist' => true
                ]);
            }
            $this->challenge_step_service->delete($id);
            return redirect()->route('admin.challenges-step.index')
                ->with('success', 'Challenge Step deleted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function attemptedUsers($id)
    {
        $challenge = $this->challenge_step_service->getChallengeStepDetails($id);
        $users = $this->challenge_step_service->getAttemptedUsers($challenge->go_session_step_id);
        return view('admin.challenge-step.attempted-users', compact('users', 'challenge'));
    }

    public function attemptedUserDetails($user_id, $go_session_step_id)
    {
        $inspiration_challenge = $this->challenge_step_service->getInspirationChallengeDetails($user_id, $go_session_step_id);
        $user = $this->challenge_step_service->attemptedUserDetails($user_id);
        return view('admin.challenge-step.attempted-user-details', compact('user', 'inspiration_challenge'));
    }

    public function challengeStatus($status, ChangeChallengeStatusRequest $request)
    {
        try {
            if($status == 'accept'){
                $this->challenge_step_service->accept($request->user_id,$request->go_session_step_id,$request->points,$request->guideline_text, $request->description);
            }else{
                $this->challenge_step_service->reject($request->user_id,$request->go_session_step_id);
            }

            //Send Firebase Notification
            try {
                $locale = userLanguage(userId: $request->user_id);
                SendFirebaseNotification::dispatch(
                    $request->user_id,
                    __('notifications.Challenge_Status.title',['Status' => $status."ed"],locale: $locale),
                    __('notifications.Challenge_Status.content',['Status' => $status."ed"],locale: $locale),
                    'Challenge_Status',['Type' => 'Challenge_Status']
                );
            }catch (\Exception $exception){}
            return $this->success(status: true, message: 'User challenges ' . $status . 'ed successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function inspirationChallengeStatus($challenge_id, $status, Request $request)
    {
        try {
            if($status == 'accept'){
                $challenge = $this->challenge_step_service->acceptInspirationChallenge($challenge_id, $request->points,$request->guideline_text);
            }else{
                $challenge = $this->challenge_step_service->rejectInspirationChallenge($challenge_id);
            }

            //Send Firebase Notification
            try {
                $locale = userLanguage(userId: $challenge->user_id);
                SendFirebaseNotification::dispatch(
                    $challenge->user_id,
                    __('notifications.Challenge_Status.title',['Status' => $status."ed"],locale: $locale),
                    __('notifications.Challenge_Status.content',['Status' => $status."ed"],locale: $locale),
                    'Challenge_Status',['Type' => 'Challenge_Status']
                );
            }catch (\Exception $exception){}
            return $this->success(status: true, message: 'User challenge ' . $status . 'ed successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getInspirationChallengeDetails($challenge_id)
    {
        $inspiration_challenge = $this->challenge_step_service->getInspirationChallengeDetailsById($challenge_id);
        $user = $this->challenge_step_service->attemptedUserDetails($inspiration_challenge->user_id);
        return view('admin.inspiration-challenges.user-challenge-detail', compact('user', 'inspiration_challenge'));
    }
}
