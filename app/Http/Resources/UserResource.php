<?php

namespace App\Http\Resources;

use App\Models\CompleteGoSessionUser;
use App\Models\GoUserProgress;
use App\Models\UserScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CarbonFootprintService;
use App\Services\UserScoreService;
use App\Services\UserTransactionService;

class UserResource extends JsonResource
{
    public function __construct(
        public $resource,
        public CarbonFootprintService $carbon_footprint_service,
        public $last_attempted_step,
        public $level,
        public $leaves,
        public $ranking,
        public UserTransactionService $user_transaction_service
    ) {}

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user_score_service = new UserScoreService(
            new UserScore(),
            new GoUserProgress(),
            new \App\Models\UserLeaveTransaction()
        );
        $this->load('userDetails.sessionTimeDuration');
        $leaves = $user_score_service->getTotalLeaves($this);
        $session_limit = $this->userDetails->sessionTimeDuration->duration;
        $total_completed_sessions = $this->user_complete_sessions_count ?? 0;
        $total_weekly_completed_sessions = 0;
        if ($this->last_attempted_step) {
            $total_weekly_completed_sessions = $this->getWeeklyCompletedSessions($this->id, $this->last_attempted_step->campaign_season_id);
        }
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'image' => $this->image ? asset($this->image) : "",
            'username' => $this->username,
            'city' => $this->city,
            'dob' => $this->dob,
            'status' => $this->status,
            'job_title' => $this->job_title,
            'session_time_duration' => $this?->userDetails?->sessionTimeDuration?->id,
            'referral_source' => $this->userDetails->referral_source,
            'is_enable_notifications' => $this->userDetails->is_enable_notifications,
            'is_enable_notifications' => $this->userDetails->is_enable_notifications,
            'language' => $this?->userDetails?->language ? new LanguageResource($this?->userDetails?->language) : null,
            'organizations' => collect(['Employee', 'Event', 'School'])
                ->map(function ($mode) {
                    $company = $this->companies->firstWhere('mode.name', $mode);
                    return $company ? new CompanyResource($company, $mode) : null;
                })
                ->filter()
                ->values(),
            'department' => $this?->department ? new CompanyDepartmentResource($this->department) : null,
            'mode' => $this?->modes ? new UserModeResource($this->modes->first()) : [],
            'is_attempt_carbon_footprints' => $this->carbon_footprint_service->getCurrentMonthCarbonFootprint($this->id),
            'carbon_footprints_values' => $this->carbon_footprint_service->getCurrentMonthCarbonFootprint($this->id, 'record'),
            'is_email_verified' => $this->email_verified_at ? true : false,
            'last_attempted_step' => $this->last_attempted_step ?? [],
            'level' => $this->level ?? [],
            'total_leaves' => $leaves,
            'ranking' => $this->ranking ?? [],
            'reward' => $this->user_transaction_service->calculateUserTotalBalance($this) ?? 0,
            'total_completed_sessions' => $total_completed_sessions,
            'is_session_limit_reached' => false,
            'invite_code' => $this->invite_code,
            'invited_by' => $this->invited_by,
            'weekly_session_goal' => $this->userDetails->sessionTimeDuration->duration ?? 0,
            'company_code' => $this->company?->code ?? null,
        ];
    }

    public function getWeeklyCompletedSessions($user_id, $campaigns_season_id)
    {
        $start_of_week = now()->startOfWeek();
        $end_of_week = now()->endOfWeek();
        return CompleteGoSessionUser::where('user_id', $user_id)
            ->where('campaigns_season_id', $campaigns_season_id)
            ->whereBetween('created_at', [$start_of_week, $end_of_week])
            ->count('go_session_id');
    }
}
