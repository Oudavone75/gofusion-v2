<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\UserLeaveTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserScore;

class UserScoreListener
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
        $data = $event->user_score;

        $campaign_season_id = $data['campaigns_season_id'] ?? $data['campaign_season_id'] ?? null;
        $column = array_key_exists('leaves', $data) ? 'leaves' : 'points';

        $user_id = $data['user_id'];
        $user = User::find($user_id);

        if (!$campaign_season_id) {
            if ($user->isCitizen()) {
                $campaign_season_id = $user->getCitizenCampaign()?->id;
            } else {
                $campaign_season_id = $user->getActiveCampanign($user->company_id)?->id;
            }
        }

        if ($user->isEmployee()) {
            $company_id = $data['company_id'] ?? $user->company_id;
            $department_id = $data['company_department_id'] ?? $user->company_department_id;
        } else {
            $company_id = null;
            $department_id = null;
        }

        // Reconcile points earned when no campaign was active
        if ($campaign_season_id) {
            app(\App\Services\UserScoreService::class)->reconcileOrphanPoints($user_id, $campaign_season_id, $company_id, $department_id);
        }

        $user_score = UserScore::where('user_id', $user_id)
            ->where('campaign_season_id', $campaign_season_id)
            ->where('company_id', $company_id)
            ->where('company_department_id', $department_id)
            ->first();

        if ($user_score && $column === 'points') {
            $user_score->points += $data[$column];
            $user_score->campaign_season_id = $campaign_season_id;
            $user_score->company_id = $company_id;
            $user_score->company_department_id = $department_id;
            $user_score->save();
        } elseif ($column === 'leaves') {
            $user_leaves_transaction = new UserLeaveTransaction();
            $user_leaves_transaction->user_id = $user_id;
            $user_leaves_transaction->leave_type = $data['leave_type'] ?? UserLeaveTransaction::LEAVE_TYPE_CREDIT;
            $user_leaves_transaction->amount = $data[$column];
            $user_leaves_transaction->reason = $data['reason'] ?? null;
            $user_leaves_transaction->save();
        } else {
            if ($column === 'points') {
                UserScore::create([
                    'user_id' => $user_id,
                    $column => $data[$column],
                    'campaign_season_id' => $campaign_season_id,
                    'company_id' => $company_id,
                    'company_department_id' => $department_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                UserLeaveTransaction::create([
                    'user_id' => $user_id,
                    'leave_type' => $data['leave_type'] ?? UserLeaveTransaction::LEAVE_TYPE_CREDIT,
                    'amount' => $data[$column],
                    'reason' => $data['reason'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
