<?php

namespace App\Traits;

use App\Jobs\SendFirebaseNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\GoSessionStep;
use App\Models\GoSession;
use App\Models\Company;
use App\Models\CompleteGoSessionUser;
use App\Models\Theme;
use App\Models\User;
use App\Models\CampaignsSeason;
use App\Models\Event;
use App\Models\UserLeaveTransaction;
use App\Models\UserScore;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait AppCommonFunction
{

    public function sendEmail($user, $email)
    {
        try {
            Mail::to($user)->send($email);
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }
    }

    protected function generateCode($name)
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6)) . rand(100, 999);
    }

    protected function generateUsername($email)
    {
        return strtok($email, '@') . rand(10, 99);
    }

    protected function getPaginatedData($query, $per_page = 10)
    {
        return $query
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($per_page)
            ->appends(request()->query());
    }

    protected function getGoSessionDetails(int $go_session_step_id)
    {
        $go_session_step = GoSessionStep::find($go_session_step_id);

        if (!$go_session_step) {
            return null;
        }

        $go_session = GoSession::find($go_session_step->go_session_id);

        if (!$go_session) {
            return null;
        }

        return [
            'go_session' => $go_session,
            'campaign_season_id' => $go_session->campaign_season_id,
        ];
    }

    protected function getUserProgressPayload($go_session_step_id, $user, $is_complete = 1): ?array
    {
        $details = $this->getGoSessionDetails($go_session_step_id);
        if (!$details) {
            return null;
        }

        return [
            'campaigns_season_id' => $details['campaign_season_id'],
            'go_session_id' => $details['go_session']->id,
            'go_session_step_id' => $go_session_step_id,
            'user_id' => $user->id,
            'is_complete' => $is_complete
        ];
    }

    protected function getUserScorePayload($go_session_step_id, $user, $points): ?array
    {
        $details = $this->getGoSessionDetails($go_session_step_id);
        if (!$details) {
            return null;
        }

        return [
            'campaigns_season_id' => $details['campaign_season_id'],
            'company_id' => $user->company_id,
            'company_department_id' => $user->company_department_id,
            'user_id' => $user->id,
            'points' => $points,
        ];
    }

    public function getAllCompanies()
    {
        return Company::query()->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('created_at', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function getAllThemes()
    {
        return Theme::query()->select('id', 'name')->get();
    }
    protected function getUserScoreLeavesPayload($go_session_step_id, $user, $leaves): ?array
    {
        $details = $this->getGoSessionDetails($go_session_step_id);
        if (!$details) {
            return null;
        }

        $go_session = $details['go_session'];

        $total_steps = $go_session->goSessionSteps->where('position', '!=', 3)->where('position', '!=', 4)->count();
        $completed_steps = $user->progresses()
        ->where('go_session_id', $go_session->id)
        ->where('campaigns_season_id', $details['campaign_season_id'])
        ->where('is_complete', 1)
        ->count();

        if ($completed_steps < $total_steps) {
            return null;
        }

        CompleteGoSessionUser::updateOrCreate([
            'campaigns_season_id' => $details['campaign_season_id'],
            'go_session_id' => $go_session->id,
            'user_id' => $user->id,
        ]);

        return [
            'campaigns_season_id' => $details['campaign_season_id'],
            'company_id' => $user->company_id,
            'company_department_id' => $user->company_department_id,
            'user_id' => $user->id,
            'leaves' => $leaves,
            'leave_type' => UserLeaveTransaction::LEAVE_TYPE_CREDIT,
            'reason' => "Feuilles attribuées pour la complétion de la session",
        ];
    }

    public function getStepPosition($go_session_id, $position)
    {
        return GoSessionStep::query()->select('id')->where('go_session_id', $go_session_id)->where('position', $position)->first();
    }

    public function fetchQueryParam($param, $default = null)
    {
        return request()->has($param) ? request()->get($param) : $default;
    }

    protected function getStepAttemptedUsers($relation, $column, $id, $search = null)
    {
        $users = User::with([
            $relation => function ($query) use ($column, $id) {
                $query->where($column, $id)
                    ->orderBy('created_at', 'desc');
            }
        ])
        ->whereHas($relation, function ($query) use ($column, $id) {
            $query->where($column, $id);
        });

        // Add search functionality
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('company.campaignSeasons', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }
        $users = $users->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->getPaginatedData($users);
    }

    public function getCompanyCampaigns($company_id)
    {
        return CampaignsSeason::select('id', 'title')
            ->where('company_id', $company_id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();
    }

    public function getActiveCampanign($company_id)
    {
        return CampaignsSeason::select('id', 'title')
            ->where('company_id', $company_id)
            ->where('status', 'active')
            ->where('end_date', '>=', date('Y-m-d'))
            ->first();
    }

    public function getCitizenCampaign()
    {
        return CampaignsSeason::select('id', 'title')
            ->where('company_id', null)
            ->where('status', 'active')
            ->where('end_date', '>=', date('Y-m-d'))
            ->first();
    }

    public function addOrUpdateChallengePoints($company_id, $campaignSeason, $points, $user)
    {
        $data = [
            'campaigns_season_id' => $campaignSeason?->id ?? null,
            'company_id' => $company_id,
            'points' => $points,
            'user_id' => $user->id
        ];
        $userScore = UserScore::where('user_id', $user->id)
            ->where('campaign_season_id', $campaignSeason?->id ?? null)
            ->where('company_id', $company_id)
            ->first();
        if ($userScore) {
            $userScore->points = $points + $userScore->points;
            $userScore->save();
            return $userScore;
        }
        return UserScore::create($data);
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function getEvents()
    {
        return Event::query()->select('id', 'title')->where('status', 'active')->where('end_date', '>=', date('Y-m-d'))->get();
    }

    protected function getSpinWheelLeavesPayload($go_session_step_id, $user, $leaves): ?array
    {
        $details = $this->getGoSessionDetails($go_session_step_id);
        if (!$details) {
            return null;
        }

        return [
            'campaigns_season_id' => $details['campaign_season_id'],
            'company_id' => $user->company_id,
            'company_department_id' => $user->company_department_id,
            'user_id' => $user->id,
            'leaves' => $leaves,
            'reason' => "Feuilles attribuées pour la roue de la fortune",
            'leave_type' => UserLeaveTransaction::LEAVE_TYPE_CREDIT,
        ];
    }

    protected function getRegisterLeavesPayload($user, $leaves): ?array
    {
        return [
            'user_id' => $user->id,
            'leaves' => $leaves,
            'reason' => "Feuilles attribuées lors de l’inscription",
            'leave_type' => UserLeaveTransaction::LEAVE_TYPE_CREDIT,
        ];
    }

    protected function addExpiryTimeForToken($user)
    {
        return $user->tokens()->latest()->first()->update([
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function updateFcmToken($userId, $token): bool|int
    {
        return User::where('id', $userId)->update(['fcm_token' => $token]);
    }
    public function deleteFcmToken($userId): bool|int
    {
        return User::where('id', $userId)->update(['fcm_token' => null]);
    }
    public function notifyCampaignUsers($campaign, $notificationType)
    {
        try {
            $companyDepartment = "company";
            $companyDepartmentName = $campaign->company->name;
            $users = User::query()->whereNotNull('fcm_token');
            if (is_null($campaign->company_id)) {
                // Send Notification to Citizens (Users with Null Company ID)
                $users->whereNull('company_id');
                $companyDepartment = "application";
                $companyDepartmentName = "";
            }

            if (!is_null($campaign->company_id)) {

                // Company-level campaign (no departments assigned)
                if ($campaign->departments()->count() === 0) {
                    // Send notification to all users of that company
                    $users->where('company_id', $campaign->company_id);
                }

                // Department-level campaign
                if ($campaign->departments()->count() > 0) {
                    // Get IDs of departments assigned to the campaign
                    $departmentIds = $campaign->departments()->pluck('company_department_id');

                    // Send notification to users belonging to these departments
                    $users->whereIn('company_department_id', $departmentIds);

                    // Optional: for display
                    $companyDepartment = "department";
                    $companyDepartmentName = $campaign->departments->pluck('name')->implode(', ');
                }
            }

            $users = $users->pluck('id')->toArray();

            $replaceableData = [
                'CampaignName' => $campaign->title,
                ...match ($notificationType) {
                    'Campaign_Activation' => [
                        'CompanyDepartment'     => $companyDepartment,
                        'CompanyDepartmentName' => $companyDepartmentName,
                    ],
                    default => [],
                },
            ];

            if (count($users) > 0) {
                foreach ($users as $userId) {
                    $locale = userLanguage(userId: $userId);
                    SendFirebaseNotification::dispatch(
                        $userId,
                        __("notifications.$notificationType.title", locale: $locale),
                        __("notifications.$notificationType.content", $replaceableData, locale: $locale),
                        "$notificationType",
                        ['Type' => "$notificationType"]
                    );
                }
            }
        } catch (\Exception $exception) {
        }
    }

    private function rankWithTiebreaker($baseQuery, $userPoints, $userCreatedAt)
    {
        $ahead = (clone $baseQuery)
            ->where(function ($q) use ($userPoints, $userCreatedAt) {
                $q->where('points', '>', $userPoints)
                    ->orWhere(function ($q2) use ($userPoints, $userCreatedAt) {
                        $q2->where('points', '=', $userPoints)
                            ->where('created_at', '<', $userCreatedAt); // earlier beats later
                    });
            })
            ->count();

        return $ahead + 1;
    }

    public function preparedEventData($validated_data, $format = "event-step")
    {
        if ($format == "event") {
            $eventData = [
                'title'             => $validated_data['event_name'],
                'description'       => $validated_data['description'],
                'event_type'        => $validated_data['event_type'],
                'location'          => $validated_data['event_location'],
                'start_date'        => Carbon::parse($validated_data['event_start_date']),
                'end_date'          => Carbon::parse($validated_data['event_end_date']),
                'status'            => 'active',
                'short_description' => isset($validated_data['guideline_text']) ? $validated_data['guideline_text'] : null,
            ];
            if (isset($validated_data['image_path'])) {
                $eventData['image'] = $validated_data['image_path'];
            }
            return $eventData;
        }

        $eventStepData = [
            'go_session_step_id'    => $validated_data['go_session_step_id'],
            'guideline_text'        => $validated_data['guideline_text'],
            'points'                => $validated_data['points'],
            'description'           => $validated_data['description']
        ];

        if (isset($validated_data['image_path'])) {
            $eventStepData['image_path'] = $validated_data['image_path'];
        }
        return $eventStepData;
    }

    public function checkUserWeeklySessionCount($user, $sessionTimeDuration, $campaignSeasonId)
    {
        $totalWeeklyAttemptSession = CompleteGoSessionUser::where('user_id', $user->id)
            ->where('campaigns_season_id', $campaignSeasonId)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();
        if ($totalWeeklyAttemptSession >= $sessionTimeDuration->duration) {
            return false;
        }
        return true;
    }

    public function findCompany($id)
    {
        return Company::find($id);
    }

    public function getRolesPermissions($guard_name)
    {
        $excluded_roles = ['Admin', 'Company Admin', 'User'];
        $roles = Role::with('permissions')->whereNotIn('name', $excluded_roles)->where('guard_name', $guard_name)->get();
        $permissions = Permission::where('guard_name', $guard_name)->get();

        return compact('roles', 'permissions');
    }

    public function getAnyAuthenticatedUser()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }

        return null; // means unauthorized
    }

    public function generateUniqueInviteCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
        } while (User::where('invite_code', $code)->exists());

        return $code;
    }

    public function getDetailedUserStats($user)
    {
        $current_campaign_season = null;
        if ($user->isCitizen()) {
            $current_campaign_season = CampaignsSeason::where('company_id', null)->where('status', 'active')->first();
        } else {
            $current_campaign_season = CampaignsSeason::where('company_id', $user->company_id)->where('status', 'active')->first();
        }

        $user_score_service = app(\App\Services\UserScoreService::class);
        $user_transaction_service = app(\App\Services\UserTransactionService::class);
        $carbon_footprint_service = app(\App\Services\CarbonFootprintService::class);

        $last_attempted_step = [];
        $ranking = [
            'campaign_or_season_wise_raking' => ['points' => 0, 'rank' => 0],
            'company_wise_ranking' => ['points' => 0, 'rank' => 0],
            'department_wise_ranking' => ['points' => 0, 'rank' => 0]
        ];
        $level = config('constants.LEVELS.10');

        if ($current_campaign_season) {
            $user = User::with(['userDetails', 'company.mode', 'department', 'modes'])->withCount([
                'userCompleteSessions as user_complete_sessions_count' => function ($query) use ($current_campaign_season) {
                    $query->where('campaigns_season_id', $current_campaign_season->id);
                }
            ])->find($user->id);

            $request_data = ['campaign_season_id' => $current_campaign_season->id];
            $last_attempted_step = $user_score_service->getUserLastAttemptedStep($request_data, $user);
            $ranking = $user_score_service->getUserRanking($request_data, $user);
            $level = $user_score_service->getUserLevel(config('constants.LEVELS'), $current_campaign_season, $user);
        }

        $user_leaves = $user_score_service->getTotalLeaves($user);

        return [
            'user' => $user,
            'carbon_footprint_service' => $carbon_footprint_service,
            'last_attempted_step' => $last_attempted_step ?? [],
            'level' => $level,
            'user_leaves' => $user_leaves,
            'ranking' => $ranking,
            'user_transaction_service' => $user_transaction_service
        ];
    }
}
