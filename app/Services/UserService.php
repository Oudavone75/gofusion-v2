<?php

namespace App\Services;

use App\Enums\RolesEnum;
use App\Http\Resources\MentionUserResource;
use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\Mode;
use App\Models\User;
use App\Models\UserCarbonFootprint;
use App\Models\GoUserProgress;
use App\Models\UserDetail;
use App\Models\UserMode;
use Illuminate\Support\Facades\Hash;
use App\Services\CarbonFootprintService;
use App\Traits\AppCommonFunction;
use App\Traits\OTPTrait;
use Illuminate\Support\Facades\DB;
use App\Services\UserTransactionService;
use Spatie\Permission\Models\Role;

use function Symfony\Component\Clock\now;

class UserService
{
    use OTPTrait, AppCommonFunction;

    public function __construct(
        private User $user,
        private UserDetail $user_detail,
        private Mode $mode,
        private UserMode $user_mode,
        private UserCarbonFootprint $user_carbon_footprint,
        private CarbonFootprintService $carbon_footprint_service,
        public UserTransactionService $user_transaction_service
    ) {
        $this->user = $user;
        $this->user_detail = $user_detail;
        $this->mode = $mode;
        $this->user_mode = $user_mode;
        $this->user_carbon_footprint = $user_carbon_footprint;
        $this->user_transaction_service = $user_transaction_service;
    }

    public function createUser($request = [])
    {
        $user_data = $this->buildUserData($request);
        $user = $this->user::create($user_data);
        $user->assignRole('User');
        $user_details = $this->buildUserDetails($request, $user);
        $this->user_detail::create($user_details);
        $this->attachDefaultMode($user);
        if (isset($request['carbon_unit']) && isset($request['carbon_value']) && isset($request['water_unit']) && isset($request['water_value'])) {
            $this->addCarbonFootprint($request, $user);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        $otp = $this->generateOtp();
        $this->createOtp($user, $otp, 'email_verification');
        $this->sendOtpMail($user, $otp, '🎉 Bienvenue dans l’aventure Go Fusion ! Active ton compte dès maintenant');
        $user->last_active_at = $this->updateLastActiveAt($user);

        return [
            "user" => $user,
            "token" => $token
        ];
    }

    public function getUserIdByInviteCode($invite_code)
    {
        $user = $this->user->where('invite_code', $invite_code)->first();
        return $user ? $user->id : null;
    }

    public function addCarbonFootprint($request, $user)
    {
        return $this->user_carbon_footprint::create([
            'user_id' => $user->id,
            'attempt_at' => now(),
            'carbon_unit' => $request['carbon_unit'],
            'carbon_value' => $request['carbon_value'],
            'water_unit' => $request['water_unit'],
            'water_value' => $request['water_value']
        ]);
    }

    public function attachDefaultMode($user)
    {
        $mode = $this->mode->where('name', 'Citizen')->first();
        return $user->modes()->sync([
            'mode_id' => $mode->id
        ]);
    }

    public function buildUserDetails($request, $user)
    {
        $language_id = $request['language_id'] ?? $this->getDefaultLanguageId();
        $referral_code = \Str::uuid();
        return [
            "session_time_duration_id" => $request['session_time_duration_id'],
            "referral_source" => $request['referral_source'],
            "is_enable_notifications" => $request['is_enable_notifications'],
            "language_id" => $language_id,
            "refered_by" => $request['refered_by'] ?? null,
            "referral_source" => $request['referral_source'] ?? null,
            "rererral_code" => $referral_code,
            "user_id" => $user->id,
        ];
    }

    public function getDefaultLanguageId()
    {
        return Language::where('label', 'fr')->first()?->id ?? 1;
    }

    public function buildUserData($request)
    {
        return [
            "first_name" => $request['first_name'],
            "last_name" => $request['last_name'] ?? null,
            "username" => $request['username'],
            "city" => $request['city'],
            "dob" => $request['dob'],
            "email" => $request['email'],
            "password" => Hash::make($request['password']),
            "work_email" => $request['work_email'] ?? $request['email'],
            "fcm_token" => $request['fcm_token'] ?? null,
            "invite_code" => $request['invite_code'] ?? null,
            "invited_by" => $request['invited_by'] ?? null,
            "join_token_id" => $request['join_token_id'] ?? null,
        ];
    }

    public function changeLanguage($request = [], $user = null)
    {
        $user_details = $this->user_detail::where('user_id', $user->id)->first();
        $user_details->language_id = $request['language_id'];
        $user_details->save();
        return $user_details;
    }

    public function assignUserMode($request = [], $user)
    {
        $company = $this->findCompany($request['company_id'] ?? null);
        if (isset($request['company_id']) && $request['user_mode_id'] !== $company->mode_id) {
            $translatedMode = __('general.mode_' . strtolower($company->mode->name));
            return [
                'success' => false,
                'message' => __('general.company_mode_restriction', ['mode' => $translatedMode]),
                'data' => null
            ];
        }
        $user->modes()->sync([
            'mode_id' => $request['user_mode_id']
        ]);
        $companyDepartmentId = $request['company_department_id'] ?? null;

        if (!$companyDepartmentId && isset($request['company_id'])) {
            $pivot = $user->companies()
                ->where('company_id', $request['company_id'])
                ->first()?->pivot;

            $companyDepartmentId = $pivot?->company_department_id;
        }

        if (isset($request['company_id'])) {
            $user->company_id = $request['company_id'];
            $user->company_department_id = $companyDepartmentId;
        }
        if (isset($request['job_title'])) {
            $user->job_title = $request['job_title'];
        }
        if (isset($request['join_token_id'])) {
            $user->join_token_id = $request['join_token_id'];
        }
        $user->save();
        $user_response = new UserResource($user, $this->carbon_footprint_service, null, null, null, null, $this->user_transaction_service);
        return $user_response;
    }

    public function getUserStats($request = []): array
    {
        $user_id = auth()->id();
        $go_session_id = $request['go_session_id'] ?? null;
        $campaigns_season_id = $request['campaigns_season_id'] ?? null;

        $user_progress  = GoUserProgress::query()
            ->select('campaigns_season_id', 'go_session_id', 'user_id', DB::raw('MAX(go_session_step_id) AS go_session_step_id'))
            ->where('user_id', $user_id);

        if ($go_session_id !== null) {
            $user_progress->where('go_session_id', $go_session_id);
        }

        if ($campaigns_season_id !== null) {
            $user_progress->where('campaigns_season_id', $campaigns_season_id);
        }

        $user_progress = $user_progress
            ->groupBy('campaigns_season_id', 'go_session_id', 'user_id')
            ->get();

        if ($user_progress->isEmpty()) {
            return ['success' => false, 'message' => trans('general.user_progress_not_found'), 'data' => []];
        }

        return ['success' => true, 'message' => trans('general.user_progress_fetched'), 'data' => $user_progress];
    }

    public function updateProfile($request, $user)
    {
        if ($request->image) {
            $image = $request->image;
            $username = $user->username;
            deleteFile($user->image);
            $file_name = uploadFile($image, 'public', 'user-images', $username);
            $file_name = 'storage/user-images/' . $file_name;
            $user->image = $file_name;
        }
        if ($request->first_name) {
            $user->first_name = $request->first_name;
        }
        if ($request->last_name) {
            $user->last_name = $request->last_name;
        }
        if ($request->city) {
            $user->city = $request->city;
        }
        if ($request->dob) {
            $user->dob = $request->dob;
        }
        if ($request->fcm_token) {
            $user->fcm_token = $request->fcm_token;
        }
        if ($request->old_password && $request->password) {
            $old_password = $request->old_password;
            $password = $request->password;
            $password_check = Hash::check($old_password, $user->password);
            if (!$password_check) {
                return ['success' => false, 'message' => trans('general.password_not_matched'), 'data' => []];
            }
            $user->password = Hash::make($password);
        }
        $user->save();
        return ['success' => true, 'message' => trans('general.profile_updated'), 'data' => $user];
    }

    public function getCitizens($search = null)
    {
        $query = $this->user
            ->role('User')
            ->whereHas('modes', function ($q) {
                $q->where('name', 'Citizen');
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        return $this->getPaginatedData($query);
    }

    public function getCitizensCount()
    {
        return $this->user
            ->role('User')
            ->whereHas('modes', function ($q) {
                $q->where('name', 'Citizen');
            })
            ->count();
    }
    public function toggleStatus($request, $user_id)
    {
        $status = $request['status'];
        $user = User::find($user_id);

        if (!$user) {
            return [
                'success' => false,
                'message' => trans('general.user_not_found')
            ];
        }

        $user->status = $status === 'active' ? 'active' : 'inactive';
        $user->save();

        return [
            'success' => true,
            'message' => $user->status === 'active' ? trans('general.user_activated') : trans('general.user_deactivated')
        ];
    }

    public function updateAdmin($data, $admin)
    {
        $admin->update($data);
        return $admin;
    }

    public function deleteUser($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return [
                'success' => false,
                'message' => trans('general.user_not_found')
            ];
        }

        $user->delete();
        return [
            'success' => true,
            'message' => 'User deleted successfully.'
        ];
    }

    public function getUsersList($request = [])
    {
        $search = $request['search'] ?? null;
        $query = $this->user->newQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }
        $query = $query->role(RolesEnum::USER->value());

        $users = $query->limit(10)->orderBy('username', 'ASC')->get();
        $users = MentionUserResource::collection($users);
        return [
            'success' => true,
            'message' => trans('general.users_list_fetched'),
            'data' => $users
        ];
    }
    public function updateLastActiveAt($user)
    {
        $user->last_active_at = now();
        $user->save();
    }

    public function getInviteFriendsList($user)
    {
        $users = User::where('invited_by', $user->id)
                ->selectRaw('id, CONCAT(first_name, " ", last_name) AS name, email')
                ->get();

        return $users;
    }

    public function getUserLeavesList($user, $request = [])
    {
        $month = $request['month'];
        $year = $request['year'];
        $leaves_list = $user->leaves()
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('created_at', $month);
            })
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('created_at', $year);
            })
            ->select('id', 'amount', 'leave_type', 'reason', DB::raw('DATE(created_at) as date'))
            ->orderBy('created_at', 'DESC')
            ->get();

        return $leaves_list;
    }

}
