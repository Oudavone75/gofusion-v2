<?php

namespace App\Services;

use App\Events\StoreCompleteUserSessionEvent;
use App\Models\EventSubmissionGuideline;
use App\Models\EventSubmissionStep;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Events\UserProgressEvent;
use App\Events\UserScoreEvent;
use App\Traits\AppCommonFunction;
use App\Models\ValidateLaterStep;

class EventValidationStepService
{
    use AppCommonFunction;
    public function getEventStepDetails(array $request): array
    {
        $event_step_details = EventSubmissionGuideline::with('event')->where('go_session_step_id', $request['go_session_step_id'])->latest()->first();

        if (!$event_step_details) {
            return ['success' => false, 'message' => trans('general.step_not_found'), 'data' => []];
        }
        $event_step_details['event_id'] = $event_step_details?->event?->id;
        // $event_step_details['total_participants'] = $this->countEventSubmissionSteps($event_step_details->event->id, $request['go_session_step_id']);
        // $event_step_details['images'] = $this->getUserImages($event_step_details->event->id, $request['go_session_step_id']);
        return ['success' => true, 'message' => trans('general.event_fetched'), 'data' => $event_step_details];
    }

    public function getEventSubmissionSteps($event_id, $go_session_step_id)
    {
        return EventSubmissionStep::query()
            ->where('event_id', $event_id)
            ->where('go_session_step_id', $go_session_step_id);
    }

    public function countEventSubmissionSteps($event_id, $go_session_step_id)
    {
        $event_submission_steps = $this->getEventSubmissionSteps($event_id, $go_session_step_id);
        return $event_submission_steps->count();
    }

    public function getUserImages($event_id, $go_session_step_id)
    {
        $event_submission_steps = $this->getEventSubmissionSteps($event_id, $go_session_step_id);
        $user_ids = $event_submission_steps->pluck('user_id');
        $user_images = User::query()
            ->whereIn('id', $user_ids)
            ->limit(4)
            ->pluck('image');
        return $user_images->map(function ($image) {
            return getImage($image);
        });
    }

    public function uploadEventImage($request): array
    {
        $filename = uploadFile($request->file('image'), 'public', 'events');
        $image_url = asset('storage/events/' . $filename);

        if (!$image_url) {
            return ['success' => false, 'message' => trans('general.image_not_uploaded'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.image_uploaded'), 'data' => $image_url];
    }

    public function validateEvent($request): array
    {
        try {
            $user = Auth::user();
            $event_guideline = EventSubmissionGuideline::with(['event', 'goSessionStep.goSession.campaignSeason'])
                ->where('go_session_step_id', $request->go_session_step_id)
                ->first();
            if (!$event_guideline) {
                return [
                    'success' => false,
                    'message' => trans('general.step_not_found'),
                    'data' => [],
                ];
            }

            if (!$event_guideline->event) {
                return [
                    'success' => false,
                    'message' => trans('general.event_not_exists'),
                    'data' => [],
                ];
            }
            $already_attempted = $event_guideline->event->eventStep()->where('user_id', $user->id)
                ->where('go_session_step_id', $request->go_session_step_id)
                ->exists();

            if ($already_attempted) {
                return [
                    'success' => false,
                    'message' => trans('general.already_attempted'),
                    'data' => [],
                ];
            }
            $payload = [
                'image_url' => $request->image_url,
                'challenge_id' => $request->go_session_step_id,
                'instructions' =>  $event_guideline->guideline_text,
            ];
            try {
                $response = Http::timeout(60)->retry(2, 1000)->post(
                    config('services.photo_validation.url') . '?api-key=' . config('services.photo_validation.api_key'),
                    $payload
                );
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => trans('general.image_validation_failed'),
                    'data' => [],
                ];
            }
            $validation_result = $response->json();
            if ($validation_result == true || $validation_result == false) {
                $points_awarded = $validation_result === true ? $event_guideline->points : 0;
                DB::table('event_submission_steps')->insert([
                    'event_id' => $event_guideline->event->id,
                    'go_session_step_id' => $request->go_session_step_id,
                    'user_id' => $user->id,
                    'points' => (int)$points_awarded,
                ]);

                $progress_payload = $this->getUserProgressPayload($request->go_session_step_id, $user, 1);
                if ($progress_payload) {
                    event(new UserProgressEvent($progress_payload));
                    event(new StoreCompleteUserSessionEvent($progress_payload));
                }

                $score_payload = $this->getUserScorePayload($request->go_session_step_id, $user, $points_awarded);
                if ($score_payload) {
                    event(new UserScoreEvent($score_payload));
                }

                $leaves_payload = $this->getUserScoreLeavesPayload($request->go_session_step_id, $user, 1);
                if ($leaves_payload) {
                    event(new UserScoreEvent($leaves_payload));
                }

                return [
                    'success' => true,
                    'message' => trans('general.event_validated'),
                    'data' => [
                        'earned_points' => (int)$points_awarded
                    ],
                ];
            }
            return [
                'success' => false,
                'message' => trans('general.image_validation_failed'),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    //    public function getAllEventsSteps($company_id=null)
    //    {
    //        if($company_id){
    //            $query = EventSubmissionGuideline::with('event')->withCount('attempts')
    //            ->whereHas('event', function ($q) use ($company_id) {
    //                $q->whereHas('eventGuideline', function ($q) use ($company_id) {
    //                    $q->whereHas('goSessionStep', function ($q) use ($company_id) {
    //                        $q->whereHas('goSession', function ($q) use ($company_id) {
    //                            $q->whereHas('campaignSeason', function ($q) use ($company_id) {
    //                                $q->where('company_id', $company_id);
    //                                $q->where('end_date', '>=', date('Y-m-d H:i:s'));
    //                            });
    //                        });
    //                    });
    //                });
    //            });
    //        }else{
    //            $query = EventSubmissionGuideline::with('event')->withCount('attempts')
    //            ->whereHas('event', function ($q) {
    //                $q->whereHas('eventGuideline', function ($q) {
    //                    $q->whereHas('goSessionStep', function ($q) {
    //                        $q->whereHas('goSession', function ($q) {
    //                            $q->whereHas('campaignSeason', function ($q) {
    //                                $q->where('end_date', '>=', date('Y-m-d H:i:s'));
    //                            });
    //                        });
    //                    });
    //                });
    //            });
    //        }
    //        return $this->getPaginatedData($query);
    //    }

    public function getAllEventsSteps($company_id = null)
    {
        $query = EventSubmissionGuideline::with('event')->withCount('attempts');
        if ($company_id) {
            $query->whereHas('goSessionStep.goSession.campaignSeason', function ($q) use ($company_id) {
                $q->where('company_id', $company_id)
                    ->where('end_date', '>=', date('Y-m-d'));
            });
        } else {
            $query->whereHas('goSessionStep.goSession.campaignSeason', function ($q) {
                $companyCheck = activeCampaignSeasonFilter() === 'campaign' ? 'whereNotNull' : 'whereNull';
                $q->{$companyCheck}('company_id')->where('end_date', '>=', date('Y-m-d'));
            });
        }

        return $this->getPaginatedData($query);
    }

    public function getEvents()
    {
        return Event::query()->select('id', 'title')->where('status', 'active')->where('end_date', '>=', date('Y-m-d'))->get();
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function create($request)
    {
        return EventSubmissionGuideline::create($request);
    }
    public function storeRelatedEvents($eventStep, $data)
    {
        return $eventStep->event()->create($data);
    }

    public function update($event_step, array $data)
    {
        $event_step->update($data);
        return $event_step;
    }

    public function updateRelatedEvents($eventStep, $data)
    {
        return is_null($eventStep->event) ? $eventStep->event()->create($data) : $eventStep->event()->update($data);
    }

    public function delete($id)
    {
        $event_step = EventSubmissionGuideline::findOrFail($id);
        return $event_step->delete();
    }

    public function isEventStepExists($id)
    {
        return EventSubmissionStep::where('go_session_step_id', $id)->exists();
    }

    public function getEventStepDetailsById($id)
    {
        return EventSubmissionGuideline::with('goSessionStep')->findOrFail($id);
    }

    public function getGoSessionStepId($session)
    {
        return $this->getStepPosition($session, config('constants.POSITION.THIRD'))->id;
    }

    public function isEventStepGuideLineExists($go_session_step_id, $edit_id = null)
    {
        $query = EventSubmissionGuideline::where('go_session_step_id', $go_session_step_id);
        if ($edit_id) {
            $query->where('id', '!=', $edit_id);
        }
        return $query->exists();
    }

    public function addValidateLaterStepData($request = [])
    {
        $is_validate_later_exists = ValidateLaterStep::where('user_id', $request['user_id'])
            ->where('go_session_step_id', $request['go_session_step_id'])
            ->exists();
        if ($is_validate_later_exists) {
            return ajaxResponse(status: false, message: trans('general.record_exists'));
        }
        $validate_later_step = ValidateLaterStep::create($request);
        return ajaxResponse(status: true, message: trans('general.step_saved'), data: $validate_later_step);
    }

    public function getAttemptedUsers($id)
    {
        return $this->getStepAttemptedUsers('event_attempts', 'go_session_step_id', $id);
    }
}
