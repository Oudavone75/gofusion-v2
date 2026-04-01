<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Services\EventService;
use App\Traits\ApiResponse;

class EventController extends Controller
{
    use ApiResponse;
    public function __construct(private EventService $event_service) {}
    public function createEvent(EventStoreRequest $request)
    {
        try {
            $response = $this->event_service->storeEventByCrawler($request->all());
            if ($response['status'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
        } catch (\Throwable $th) {
            return $this->error(false, $th->getMessage());
        }
        return $this->success(status: true, message: 'Event created successfully!', result: $response['data']);
    }
}
