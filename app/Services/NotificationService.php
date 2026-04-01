<?php

namespace App\Services;

use App\Models\Notification;
use Carbon\Carbon;
use App\Jobs\SendFirebaseNotification;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;

class NotificationService
{
    use AppCommonFunction;

    public function getUserNotifications($userId)
    {
        return Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('users', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    });
            })
            ->where('status', 'sent')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getNotificationList($company_id = null)
    {
        $query = Notification::query()
            ->where('type', 'General')
            ->orderByDesc('created_at')
            ->withCount('users');

        if (!is_null($company_id)) {
            $query->where('company_id', $company_id);
        }

        if (activeCampaignSeasonFilter() === 'campaign') {
            $query->whereNotNull('company_id');
        } else {
            $query->whereNull('company_id');
        }

        return $this->getPaginatedData($query);
    }

    public function createNotification(array $data)
    {
        try {
            $users = User::when(isset($data['company_id']), function ($q) use ($data) {
                if (is_null($data['company_id'])) {
                    $q->whereNull('company_id');
                } else {
                    $q->where('company_id', $data['company_id']);
                }})
                ->when(!empty($data['departments']), function ($q) use ($data) {
                    $q->whereIn('company_department_id', $data['departments']);
                })
                ->get();

            if ($users->isEmpty()) {
                return [
                    'status' => false,
                    'message' => 'No users found for the selected company and department.'
                ];
            }

            $is_scheduled = $data['notification_type'] === 'scheduled';
            $scheduled_at = $is_scheduled && !empty($data['scheduled_at'])
                ? Carbon::parse($data['scheduled_at'])
                : null;

            $notification = Notification::create([
                'company_id'         => $data['company_id'] ?? null,
                'notification_type'  => $data['notification_type'],
                'scheduled_at'       => $scheduled_at,
                'status'             => $is_scheduled ? 'scheduled' : 'sent',
                'sent_at'            => $is_scheduled ? null : now(),
                'title'              => $data['title'],
                'content'            => $data['content'],
                'type'               => 'General',
                'data'               => ['Type' => 'General'],
            ]);
            $notification->users()->attach($users->pluck('id')->toArray());
            if (isset($data['departments'])) {
                $notification->departments()->sync($data['departments']);
            }

            foreach ($users as $user) {
                $job = SendFirebaseNotification::dispatch(
                    $user->id,
                    $data['title'],
                    $data['content'],
                    'General',
                    ['Type' => 'General'],
                    $notification->id
                );

                if ($is_scheduled) {
                    $job->delay($scheduled_at);
                }
            }

            return [
                'status' => true,
                'message' => $is_scheduled
                    ? 'Notification scheduled successfully.'
                    : 'Notification sent successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Notification creation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to process notification: ' . $e->getMessage()
            ];
        }
    }

    public function getNotificationById($id)
    {
        return Notification::withCount('users')->find($id);
    }

    public function getNotificationRecipients($id)
    {
        $query = Notification::find($id)->users();
        return $this->getPaginatedData($query);
    }

    public static function sendMentionNotifications($authUser, $userIds, $comment)
    {
        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    SendFirebaseNotification::dispatch(
                        $user->id,
                        trans('notifications.mention_notification.title'),
                        trans(
                            'notifications.mention_notification.content',
                            ['MentionedBy' => $authUser->first_name . ' ' . $authUser->last_name]
                        ),
                        'Mention',
                        ['Type' => 'Mention', 'MentionedBy' => $authUser->id, 'CommentId' => $comment->id],
                    );
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to send mention notifications: ' . $e->getMessage());
        }
    }
}
