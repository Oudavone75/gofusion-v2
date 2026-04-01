<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreImageStepRequest;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Services\ImageValidationStepService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendFirebaseNotification;
use App\Events\UserScoreEvent;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;
use App\Models\ImageSubmissionStep;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImageStepController extends Controller
{
    use ApiResponse, AppCommonFunction;
    private $user;
    public function __construct(private ImageValidationStepService $image_step_service)
    {
        $this->user  = Auth::user();
    }
    public function index()
    {
        $company_id = $this->user->company_id;
        $image_steps = $this->image_step_service->getAllImageSteps($company_id);
        return view('company_admin.session-steps.image-step.index', compact('image_steps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CampaignSeasonService $campaign_season_service)
    {
        $company_id = $this->user->company_id;
        $campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        return view('company_admin.session-steps.image-step.create', compact('campaigns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageStepRequest $request)
    {
        try {
            $go_session_step_id = $this->image_step_service->getGoSessionStepId($request->session);
            $is_already_exist = $this->image_step_service->isImgageStepGuideLineExists($go_session_step_id);
            if ($is_already_exist) {
                return $this->error(status: false, message: 'Image Step already exists for this session.', code: 500);
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            if ($request->hasFile('image')) {
                $filename = uploadFile($request->file('image'), 'public', 'image-step');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image_path'] = asset('storage/image-step/' . $filename);
            }
            $this->image_step_service->create($validated_data);
            return $this->success(status: true, message: 'Image Step created successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $image_step = $this->image_step_service->getImageStepDetailsById($id);
        return view('company_admin.session-steps.image-step.view', compact('image_step'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, CampaignSeasonService $campaign_season_service, GoSessionService $go_session_service)
    {
        $image_step = $this->image_step_service->getImageStepDetailsById($id);
        $company_id = $image_step->goSessionStep->goSession->campaignSeason->company_id;
        $company_campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        $company_campaigns_id = $image_step->goSessionStep->goSession->campaignSeason->id;
        $campaign_sessions = $go_session_service->getGoSessions($company_campaigns_id);
        return view('company_admin.session-steps.image-step.edit', compact('image_step', 'company_campaigns', 'campaign_sessions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreImageStepRequest $request, $id)
    {
        try {
            $go_session_step_id = $this->image_step_service->getGoSessionStepId($request->session);
            $is_data_exist_of_this_image_step = $this->image_step_service->isImgageStepExists($go_session_step_id);
            if ($is_data_exist_of_this_image_step) {
                return $this->error(status: false, message: 'Some users already attempted this step so you cannot edit this step.');
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            $image_step = $this->image_step_service->getImageStepDetailsById($id);
            if ($request->hasFile('image')) {
                if (!empty($image_step->image_path)) {
                    $oldImagePath = public_path('storage/image-step/' . basename($image_step->image_path));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $filename = uploadFile($request->file('image'), 'public', 'image-step');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image_path'] = asset('storage/image-step/' . $filename);
            }
            if ($validated_data['mode'] === 'video') {
                $validated_data['image_path'] = null;
                $validated_data['guideline_text'] = null;
            } else if ($validated_data['mode'] === 'photo') {
                $validated_data['video_url'] = null;
            } else {
                $validated_data['image_path'] = null;
                $validated_data['guideline_text'] = null;
                $validated_data['video_url'] = null;
            }
            $this->image_step_service->update($image_step, $validated_data);
            return $this->success(status: true, message: 'Image Step updated successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $image_step_details = $this->image_step_service->getImageStepDetailsById($id);
            $is_data_exist_of_this_image_step = $this->image_step_service->isImgageStepExists($image_step_details->go_session_step_id);
            if ($is_data_exist_of_this_image_step) {
                return response()->json([
                    'record_exist' => true
                ]);
            }
            $this->image_step_service->delete($id);
            return redirect()->route('company_admin.session-steps.images.index')
                ->with('success', 'Image Step deleted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function attemptedUsers($id)
    {
        $image_detail = $this->image_step_service->getImageStepDetailsById($id);
        $users = $this->image_step_service->getAttemptedUsers($image_detail->go_session_step_id);
        return view('company_admin.session-steps.image-step.attempted-users', compact('users', 'image_detail'));
    }

    public function appealingUsers($id)
    {
        $image_detail = $this->image_step_service->getImageStepDetailsById($id);
        $image_step = $this->image_step_service->getImageStepDetailsByGoSessionStepId($image_detail->go_session_step_id);
        $users = $this->image_step_service->getAppealingUsers($image_detail->go_session_step_id);
        return view('company_admin.session-steps.image-step.appealing-users', compact('users', 'image_detail', 'image_step'));
    }

    public function changeAppealingStatus(Request $request, ImageSubmissionStep $image_step)
    {
        try {
            $user = $image_step->user;
            $status = $request->status;
            $data = [
                'status' => $status === "approve" ? 'approved' : 'rejected',
            ];

            if ($status === "approve") {
                $data['points'] = $request->points;
                $message = 'Challenge approved successfully!';
            }
            else {
                $data['points'] = 0;
                if ($request->has('reason') && !empty($request->reason)) {
                    $data['rejection_reason'] = $request->reason;
                }
                $message = 'Challenge rejected successfully!';
            }

            $this->image_step_service->update(
                image_step: $image_step,
                data: $data
            );

            $score_payload = $this->getUserScorePayload($image_step->go_session_step_id, $user, $data['points']);
            if ($score_payload) {
                event(new UserScoreEvent($score_payload));
            }

            $locale = userLanguage(userId: $user->id);

            $status_text = $status === 'approve' ? __('Approved', locale: $locale) : __('Rejected', locale: $locale);

            // Prepare the base title and content
            $title = __('notifications.Image_Challenge.title', [
                'Status' => $status_text
            ], $locale);

            $content = __('notifications.Image_Challenge.content', [
                'Status' => strtolower($status_text)
            ], $locale);

            // Append rejection reason if available
            if ($status === 'reject' && !empty($request->reason)) {
                $content .= ' — ' . __('Reason:', locale: $locale) . ' ' . $request->reason;
            }

            // Send Firebase notification
            try {
                SendFirebaseNotification::dispatch(
                    $user->id,
                    $title,
                    $content,
                    'Image_Challenge',
                    ['Type' => 'Image_Challenge']
                );
            } catch (\Exception $exception) {
                // Log or ignore
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function attemptedUserDetails($user_id, $go_session_step_id)
    {
        $image_challenge = $this->image_step_service->getImageChallengeDetails($user_id, $go_session_step_id);
        $user = $this->image_step_service->attemptedUserDetails($user_id);
        return view('company_admin.session-steps.image-step.attempted-user-details', compact('user', 'image_challenge'));
    }

    public function export(Request $request, $id)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
                'type'       => 'required|in:excel,csv'
            ]);

            $start_date = $request->start_date;
            $end_date   = $request->end_date ?? $start_date;
            // dd( $start_date, $end_date);

            $extension = $request->type === 'csv' ? 'csv' : 'xlsx';

            if ($start_date && $end_date) {
                $file_name = 'image_step_users_'
                    . Carbon::parse($start_date)->format('Y-m-d')
                    . '_to_'
                    . Carbon::parse($end_date)->format('Y-m-d')
                    . '.' . $extension;
            } else {
                $file_name = 'image_step_users_all.' . $extension;
            }

            return $this->image_step_service->export(
                $start_date,
                $end_date,
                $file_name,
                $id,
                $request->type
            );

        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Something went wrong while exporting. Please try again.');
        }
    }
}
