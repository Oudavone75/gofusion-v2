<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\EventValidationStepService;
use App\Http\Requests\EventStepDetailsRequest;
use App\Http\Requests\ValidateChallengeRequest;
use App\Http\Requests\ValidateEventRequest;
use App\Http\Requests\UploadChallengeImageRequest;
use App\Http\Requests\ValidateLaterStepRequest;
use Illuminate\Support\Facades\Auth;

class EventValidationStepController extends Controller
{
    use ApiResponse;
    public function __construct(public EventValidationStepService $eventValidationStepService) {}

    public function getEventStepDetails(EventStepDetailsRequest $request)
    {
        try {
            $eventStepDetails = $this->eventValidationStepService->getEventStepDetails($request->all());
            if ($eventStepDetails['success'] === false) {
                return $this->error(status: false, message: $eventStepDetails['message']);
            }
            return $this->success(status: true, message: $eventStepDetails['message'], result: $eventStepDetails['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function uploadEventImage(UploadChallengeImageRequest $request)
    {
        try {
            $event_image = $this->eventValidationStepService->uploadEventImage($request);
            return $this->success(status: true, message: $event_image['message'], result: $event_image['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function validateEvent(ValidateEventRequest $request)
    {
        try {
            $validate_event = $this->eventValidationStepService->validateEvent($request);
            if ($validate_event['success'] === false) {
                return $this->error(status: false, message: $validate_event['message'], code: 400);
            }
            return $this->success(status: true, message: $validate_event['message'], result: $validate_event['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function validateEventLater(ValidateLaterStepRequest $request)
    {
        try {
            $request_data = $request->all();
            $user = Auth::user();
            $request_data['user_id'] = $user->id;
            $response = $this->eventValidationStepService->addValidateLaterStepData($request_data);
            if ($response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
        return $this->success(status: true, message: $response['message'], result: ['data' => $response['data']]);
    }
}
