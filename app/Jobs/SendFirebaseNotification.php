<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotification implements ShouldQueue
{
    use Queueable;
    public $userId;
    public $title;
    public $content;
    public $type;
    public $data;
    public $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $title, $content, $type, $data = [], $notificationId = null)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->data = $data;
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        // Send notification (only if FCM token exists)
        if ($user->fcm_token) {
            try {
                $user->sendNotification(
                    title: $this->title,
                    message: $this->content,
                    data: $this->data
                );
            } catch (\Exception $ex) {
                Log::error("❌ Firebase send failed: " . $ex->getMessage());
            }
        }

        // Create user notification history
        try {
            if ($this->notificationId == null) {
                Notification::create([
                    'user_id'   => $user->id,
                    'title'     => $this->title,
                    'content'   => $this->content,
                    'type'      => $this->type,
                    'data'      => $this->data,
                    'sent_at' => now(),
                    'status' => 'sent',
                ]);
            }
        } catch (\Exception $exception) {
            Log::error("❌ Notification create failed: " . $exception->getMessage());
        }

        // Try to update scheduled notification
        if ($this->notificationId) {
            Notification::where('id', $this->notificationId)->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } else {
            Log::warning("⚠️ No notificationId provided to update status");
        }

        Log::info("🏁 Job completed for user ID: {$this->userId}");
    }
}
