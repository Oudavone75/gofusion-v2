<?php

namespace App\Console\Commands;

use App\Jobs\SendFirebaseNotification;
use App\Models\CampaignsSeason;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendSessionReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-session-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications to users who have not completed their required weekly sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start_of_current_week = now()->startOfWeek();
        $end_of_current_week = now()->endOfWeek();
        $active_campaign_season_ids = CampaignsSeason::where('status', 'active')->pluck('id');

        $user_ids = DB::table('complete_go_session_users')
            ->whereIn('campaigns_season_id', $active_campaign_season_ids)
            ->whereBetween('created_at', [$start_of_current_week, $end_of_current_week])
            ->select('user_id')
            ->groupBy('user_id')
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($user_ids->count() > 0) {
            foreach ($user_ids as $user_id) {
                $user = User::with([
                    'userDetails.sessionTimeDuration'
                ])->withCount([
                    'userCompleteSessions as user_complete_sessions_current_week_count' => function ($q) use ($start_of_current_week, $end_of_current_week) {
                        $q->whereBetween('created_at', [$start_of_current_week, $end_of_current_week]);
                    }
                ])->find($user_id);

                if ($user && $user->userDetails && $user->userDetails->sessionTimeDuration && $user->userDetails->sessionTimeDuration->duration > $user->user_complete_sessions_current_week_count) {
                    $required_sessions = $user->userDetails->sessionTimeDuration->duration;
                    $completed_sessions = $user->user_complete_sessions_current_week_count;
                    $remaining_sessions = $required_sessions - $completed_sessions;

                    // Send Firebase Notification
                    try {
                        $locale = userLanguage(userId: $user_id);
                        SendFirebaseNotification::dispatch(
                            $user_id,
                            __('notifications.Session_Reminder.title', locale: $locale),
                            __('notifications.Session_Reminder.content', [
                                'RequiredSessions' => $required_sessions,
                                'CompletedSessions' => $completed_sessions,
                                'RemainingSessions' => $remaining_sessions
                            ], locale: $locale),
                            'Session_Reminder',
                            ['Type' => 'Session_Reminder']
                        );

                        $this->info("Reminder sent to user ID: {$user_id} (Completed: {$completed_sessions}/{$required_sessions})");
                    } catch (\Exception $exception) {
                        Log::channel('firebase_notifications')->error('Firebase notification failed for session reminder', [
                            'user_id' => $user_id,
                            'required_sessions' => $required_sessions,
                            'completed_sessions' => $completed_sessions,
                            'error_message' => $exception->getMessage(),
                            'error_trace' => $exception->getTraceAsString(),
                        ]);

                        $this->error("Failed to send reminder to user ID: {$user_id}");
                    }
                }
            }

            $this->info('Session reminder notifications sent successfully.');
        } else {
            $this->info('No users found who need reminders.');
        }
    }
}
