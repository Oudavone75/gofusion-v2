<?php

namespace App\Console\Commands;

use App\Jobs\SendFirebaseNotification;
use App\Models\CampaignsSeason;
use App\Models\GoUserProgress;
use App\Models\User;
use App\Models\UserLeaveTransaction;
use App\Models\UserScore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveDeductionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:leave-deduction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deduct leaves from users who did not complete their required weekly sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start_of_last_week = now()->subWeek()->startOfWeek();
        $end_of_last_week = now()->subWeek()->endOfWeek();
        $active_campaign_season_ids = CampaignsSeason::where('status', 'active')->pluck('id');
        $user_ids = DB::table('complete_go_session_users')
            ->whereIn('campaigns_season_id', $active_campaign_season_ids)
            ->whereBetween('created_at', [$start_of_last_week, $end_of_last_week])
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
                    'userCompleteSessions as user_complete_sessions_last_week_count' => function ($q) use ($start_of_last_week, $end_of_last_week) {
                        $q->whereBetween('created_at', [$start_of_last_week, $end_of_last_week]);
                    }
                ])->find($user_id);
                if ($user && $user->userDetails && $user->userDetails->sessionTimeDuration && $user->userDetails->sessionTimeDuration->duration > $user->user_complete_sessions_last_week_count) {
                    $user_leaves = UserLeaveTransaction::where('user_id', $user_id)->sum('amount');
                    if ($user_leaves && $user_leaves > 0) {
                        $leaves_to_deduct = -1;
                        $user_leave_transaction = new UserLeaveTransaction();
                        $user_leave_transaction->user_id = $user_id;
                        $user_leave_transaction->leave_type = UserLeaveTransaction::LEAVE_TYPE_DEBIT;
                        $user_leave_transaction->amount = $leaves_to_deduct;
                        $user_leave_transaction->reason = 'La condition de la session hebdomadaire n’est pas remplie.';
                        $user_leave_transaction->save();
                        //Send Firebase Notification
                        try {
                            $locale = userLanguage(userId: $user_id);
                            SendFirebaseNotification::dispatch(
                                $user_id,
                                __('notifications.Leave_Deducted.title',locale: $locale),
                                __('notifications.Leave_Deducted.content',['NumberOfLeaves' => 1],locale: $locale),
                                'Leave_Deducted',['Type' => 'Leave_Deducted']
                            );
                        } catch (\Exception $exception) {
                            Log::channel('firebase_notifications')->error('Firebase notification failed for leave deduction', [
                                'user_id' => $user_id,
                                'error_message' => $exception->getMessage(),
                                'error_trace' => $exception->getTraceAsString(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
