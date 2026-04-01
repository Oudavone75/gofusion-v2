<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Traits\AppCommonFunction;
use App\Traits\ApiResponse;
use App\Http\Requests\NotificationRequest;

class NotificationController extends Controller
{
    use AppCommonFunction, ApiResponse;
    public function __construct(private NotificationService $notification_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = $this->getCompanies();
        $notifications = $this->notification_service->getNotificationList($request->company_id);
        return view('admin.notifications.index', compact('notifications', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = $this->getCompanies();

        return view('admin.notifications.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NotificationRequest $request)
    {
        $data = $request->validated();

        try {
            $response = $this->notification_service->createNotification($data);

            return response()->json([
                'redirect' => route('admin.notifications.index'),
                'status' => true,
                'message' => $response['message'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notification = $this->notification_service->getNotificationById($id);
        return view('admin.notifications.view', compact('notification'));
    }

    public function getRecipients(string $id)
    {
        $recipients = $this->notification_service->getNotificationRecipients($id);
        return view('admin.notifications.recipients', compact('recipients'));
    }
}
