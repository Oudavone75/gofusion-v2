<?php

namespace App\Listeners;

use App\Jobs\SendFirebaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\GoUserProgress;

class UserProgressListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $campaign_season_id = $event->user_progress['campaign_season_id'] ?? $event->user_progress['campaigns_season_id'];
        $go_session_id = $event->user_progress['go_session_id'];
        $go_session_step_id = $event->user_progress['go_session_step_id'];
        $user_id = $event->user_progress['user_id'];
        $go_user_progress = GoUserProgress::where('campaigns_season_id', $campaign_season_id)
            ->where('go_session_id', $go_session_id)
            ->where('go_session_step_id', $go_session_step_id)
            ->where('user_id', $user_id)
            ->first();
        if (!$go_user_progress) {
            GoUserProgress::create([
                'campaigns_season_id' => $campaign_season_id,
                'go_session_id' => $go_session_id,
                'go_session_step_id' => $go_session_step_id,
                'user_id' => $user_id,
                'is_complete' => $event->user_progress['is_complete'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
// dd(GoUserProgress::query()
//             ->where('campaigns_season_id', $campaign_season_id)
//             ->where('go_session_id', $go_session_id)
//             ->where('user_id', $user_id)->count());
        if (GoUserProgress::query()
            ->where('campaigns_season_id', $campaign_season_id)
            ->where('go_session_id', $go_session_id)
            ->where('user_id', $user_id)->count() == 4){
            //Send Firebase Notification
            try {
                $locale = userLanguage(userId: $user_id);
                SendFirebaseNotification::dispatch(
                    $user_id,
                    __('notifications.Session_Completed.title',locale: $locale),
                    __('notifications.Session_Completed.content',locale: $locale),
                    'Session_Completed',['Type' => 'Session_Completed']
                );
            }catch (\Exception $exception){}
        }
    }
}
