<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventStepRequest;
use App\Services\CampaignSeasonService;
use App\Services\EventValidationStepService;
use App\Services\GoSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
class EventStepController extends Controller
{
    use ApiResponse, AppCommonFunction;
    private $user;
    public function __construct(private EventValidationStepService $event_step_service)
    {
        $this->user  = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company_id = $this->user->company_id;
        $event_steps = $this->event_step_service->getAllEventsSteps($company_id);
        return view('company_admin.session-steps.event-step.index', compact('event_steps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CampaignSeasonService $campaign_season_service)
    {
        $company_id = $this->user->company_id;
        $campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        $events = $this->event_step_service->getEvents();
        return view('company_admin.session-steps.event-step.create', compact('campaigns', 'events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventStepRequest $request)
    {
        try {
            $go_session_step_id = $this->event_step_service->getGoSessionStepId($request->session);
            $is_already_exist = $this->event_step_service->isEventStepGuideLineExists($go_session_step_id);
            if ($is_already_exist) {
                return $this->error(status: false, message: 'Event Step already exists for this session.', code: 500);
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            if ($request->hasFile('image')) {
                $filename = uploadFile($request->file('image'), 'public', 'event-step');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image_path'] = asset('storage/event-step/' . $filename);
            }
            $eventStep = $this->event_step_service->create($this->preparedEventData(validated_data: $validated_data));
            if ($eventStep) {
                $this->event_step_service->storeRelatedEvents(
                    eventStep: $eventStep,
                    data: $this->preparedEventData(validated_data: $validated_data,format: 'event')
                );
            }
            return $this->success(status: true, message: 'Event Step created successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $event_step = $this->event_step_service->getEventStepDetailsById($id);
        return view('company_admin.session-steps.event-step.view', compact('event_step'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, CampaignSeasonService $campaign_season_service, GoSessionService $go_session_service)
    {
        $event_step = $this->event_step_service->getEventStepDetailsById($id);
        $company_id = $event_step->goSessionStep->goSession->campaignSeason->company_id;
        $company_campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        $company_campaigns_id = $event_step->goSessionStep->goSession->campaignSeason->id;
        $campaign_sessions = $go_session_service->getGoSessions($company_campaigns_id);
        $events = $this->event_step_service->getEvents();
        return view('company_admin.session-steps.event-step.edit', compact('event_step', 'company_campaigns', 'campaign_sessions', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreEventStepRequest $request, $id)
    {
        try {
            $go_session_step_id = $this->event_step_service->getGoSessionStepId($request->session);
            $is_already_exist = $this->event_step_service->isEventStepGuideLineExists($go_session_step_id, $id);
            if ($is_already_exist) {
                return $this->error(status: false, message: 'Event Step already exists for this session.', code: 500);
            }
            $validated_data = $request->validated();
            $validated_data['go_session_step_id'] = $go_session_step_id;
            $event_step = $this->event_step_service->getEventStepDetailsById($id);
            if ($request->hasFile('image')) {
                if (!empty($event_step->image_path)) {
                    $oldImagePath = public_path('storage/event-step/' . basename($event_step->image_path));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $filename = uploadFile($request->file('image'), 'public', 'event-step');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image_path'] = asset('storage/event-step/' . $filename);
            }
            $eventStep = $this->event_step_service->update($event_step, $this->preparedEventData(validated_data: $validated_data));
            if ($eventStep) {
                $this->event_step_service->updateRelatedEvents(
                    eventStep: $eventStep,
                    data: $this->preparedEventData(validated_data: $validated_data,format: 'event')
                );
            }
            return $this->success(status: true, message: 'Event Step updated successfully!', code: 200);
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
            $image_step_details = $this->event_step_service->getEventStepDetailsById($id);
            $is_data_exist_of_this_image_step = $this->event_step_service->isEventStepExists($image_step_details->go_session_step_id);
            if ($is_data_exist_of_this_image_step) {
                return response()->json([
                    'record_exist' => true
                ]);
            }
            $this->event_step_service->delete($id);
            return redirect()->route('admin.images.index')
                ->with('success', 'Image Step deleted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }
    public function attemptedUsers($id)
    {
        $event_detail = $this->event_step_service->getEventStepDetailsById($id);
        $users = $this->event_step_service->getAttemptedUsers($event_detail->go_session_step_id);
        return view('company_admin.session-steps.event-step.attempted-users', compact('users', 'event_detail'));
    }
}
