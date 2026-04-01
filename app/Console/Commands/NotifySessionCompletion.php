<?php

namespace App\Console\Commands;

use App\Jobs\SendFirebaseNotification;
use App\Models\CampaignsSeason;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifySessionCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-session-completion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start_of_week = now()->startOfWeek();
        $end_of_week = now()->endOfWeek();
        $active_campaign_season_ids = CampaignsSeason::where('status', 'active')->pluck('id');
        $user_ids = DB::table('complete_go_session_users')
            ->whereIn('campaigns_season_id', $active_campaign_season_ids)
            ->whereBetween('created_at', [$start_of_week, $end_of_week])
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
                    'userCompleteSessions as user_complete_sessions_week_count' => function ($q) use ($start_of_week, $end_of_week) {
                        $q->whereBetween('created_at', [$start_of_week, $end_of_week]);
                    }
                ])->find($user_id);
                if ($user && $user->userDetails->sessionTimeDuration->duration < $user->user_complete_sessions_week_count) {
                    //Send Firebase Notification
                    try {
                        $locale = userLanguage(userId: $user_id);
                        SendFirebaseNotification::dispatch(
                            $user_id,
                            __('notifications.Session_Complete_Alert.title',locale: $locale),
                            __('notifications.Session_Complete_Alert.content',locale: $locale),
                            'Session_Complete_Alert',['Type' => 'Session_Complete_Alert']
                        );
                    }catch (\Exception $exception){}
                }
            }
        }
    }
}
