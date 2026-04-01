<?php

namespace App\Services;

use App\Models\GoSession;
use App\Models\GoSessionStep;

class UserProgressService
{

    public function getUserProgressObject($go_session_step_id = [])
    {
        $go_session_step = GoSessionStep::query()->with('goSession')->where('id', $go_session_step_id)->first();
        $go_session = $go_session_step->goSession;
        if (!$go_session) {
            return response()->json(['error' => 'GoSession or Campaign/Season not found']);
        }

        return [
            'go_session_step_id' => $go_session_step_id,
            'user_id' => auth()->user()->id,
            'go_session_id' => $go_session ? $go_session->id : null,
            'campaign_season_id' => $go_session ? $go_session->campaign_season_id : null,
            'is_complete' => 1
        ];
    }
}

