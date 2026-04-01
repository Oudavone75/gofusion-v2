<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendFirebaseNotification;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\AppCommonFunction;

class NotificationController extends Controller
{
    use ApiResponse,AppCommonFunction;

    public function __construct(public NotificationService $notification_service){}

    public function getNotificationsList()
    {
        try {
            $notifications = $this->notification_service->getUserNotifications(userId: Auth::id());
            return $this->success(
                message: 'User notification list',
                result: NotificationResource::collection($notifications),
            );
        }catch (\Exception $exception){
            return $this->error(false, $exception->getMessage());
        }
    }

    public function test(Request $request)
    {
        $title = $request->input('title') ?? "Testing by Qamar Kayani";
        $content = $request->input('content') ?? "Testing by Qamar Kayani";
        $type = $request->input('type') ?? "TestByQK";
        try {
            SendFirebaseNotification::dispatch(Auth::id(),$title,$content,$type,["Type" => $type]);

            return $this->success(
                message: 'Notification sent successfully!',
                result: [],
            );
        }catch (\Exception $exception){
            return $this->error(false, $exception->getMessage());
        }
    }
}
