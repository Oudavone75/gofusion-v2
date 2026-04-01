<?php

namespace App\Listeners;

use App\Models\CompleteGoSessionUser;
use App\Models\GoUserProgress;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreCompleteUserSessionListner
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
        $data = $event->data;
        $campaign_season_id = $data['campaigns_season_id'] ?? $data['campaign_season_id'];
        $user_progress_count = GoUserProgress::where('user_id', $data['user_id'])
            ->where('go_session_id', $data['go_session_id'])
            ->where('campaigns_season_id', $campaign_season_id)
            ->count();
        if ($user_progress_count == 4){
            CompleteGoSessionUser::firstOrCreate([
                'campaigns_season_id' => $campaign_season_id,
                'user_id' => $data['user_id'],
                'go_session_id' => $data['go_session_id']
            ]);
        }
    }
}
