<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendFirebaseNotification;
use App\Models\City;
use App\Models\Country;
use App\Models\SessionTimeDuration;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Language;
use Illuminate\Support\Facades\Auth;
use App\Services\Auth\LoginService;
use App\Services\Auth\ForgotPasswordService;
use App\Services\Auth\OtpVerificationService;
use App\Services\Auth\ResendOTPService;
use App\Services\Auth\ResetPasswordService;
use App\Services\CompanyService;
use App\Services\UserScoreService;
use App\Events\UserScoreEvent;
use App\Http\Resources\ModeResource;
use App\Http\Resources\UserResource;
use App\Models\CampaignsSeason;
use App\Models\Mode;
use App\Services\CarbonFootprintService;
use App\Services\UserTransactionService;
use App\Traits\AppCommonFunction;

class AuthController extends Controller
{
    use ApiResponse, AppCommonFunction;

    public function __construct(
        public UserService $user_service,
        public LoginService $login_service,
        public ForgotPasswordService $forgot_password_service,
        public OtpVerificationService $otp_verification_Service,
        public ResendOTPService $resend_otp_service,
        public ResetPasswordService $reset_password_service,
        private CompanyService $company_service,
        public UserScoreService $user_score_service,
        private CarbonFootprintService $carbon_footprint_service,
        public UserTransactionService $user_transaction_service,
        private \App\Services\CompanyJoinTokenService $join_token_service
    ) {}

    public function register(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|max:255',
                'last_name' => 'nullable|max:255',
                'username' => 'required|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'city' => 'required|max:255',
                'dob' => 'required|date|before_or_equal:today',
                'session_time_duration_id' => 'required',
                'referral_source' => 'nullable|max:255',
                'join_token' => 'nullable|string',
                'password' => [
                    'required',
                    Password::min(8)
                        ->max(16)
                        ->letters()
                        ->symbols()
                        ->mixedCase()
                        ->numbers()
                ]
            ]);

            DB::beginTransaction();

            $invite_code = $this->generateUniqueInviteCode();

            $user_data = $request->all();
            $user_data['invite_code'] = $invite_code;

            if ($request->has('invite_code') && $request->invite_code) {
                $user_data['invited_by'] = $this->user_service->getUserIdByInviteCode($request->invite_code);
            }

            // Handle Join Token if provided
            if ($request->has('join_token') && $request->join_token) {
                $tokenId = $this->join_token_service->recordRegistration($request->join_token);
                if ($tokenId) {
                    $user_data['join_token_id'] = $tokenId;
                }
            }

            $register_response = $this->user_service->createUser($user_data);
            $response = $this->getUserDetails($request, false, $register_response['user']);

            $locale = userLanguage(userId: $register_response['user']->id);

            if ($request->has('invite_code') && $request->invite_code && !empty($user_data['invited_by'])) {
                $referral_xp = 150;
                $new_user_id = $register_response['user']->id;
                $inviter_id = $user_data['invited_by'];
                $inviter_locale = userLanguage(userId: $inviter_id);

                // Award XP to both users
                $new_user_campaign_id = $this->getCitizenCampaign()?->id;
                event(new UserScoreEvent([
                    'user_id' => $new_user_id,
                    'points' => $referral_xp,
                    'campaign_season_id' => $new_user_campaign_id
                ]));

                $inviter = User::find($inviter_id);
                $inviter_campaign_id = $inviter->isCitizen() ? $this->getCitizenCampaign()?->id : $this->getActiveCampanign($inviter->company_id)?->id;
                event(new UserScoreEvent([
                    'user_id' => $inviter_id,
                    'points' => $referral_xp,
                    'campaign_season_id' => $inviter_campaign_id,
                    'company_id' => $inviter->company_id,
                    'company_department_id' => $inviter->company_department_id
                ]));

                // Send XP bonus notifications
                try {
                    SendFirebaseNotification::dispatch(
                        $new_user_id,
                        __('notifications.Referral_XP_Bonus.title', ['Points' => $referral_xp], $locale),
                        __('notifications.Referral_XP_Bonus.content', ['Points' => $referral_xp], $locale),
                        'Referral_XP_Bonus',
                        ['Type' => 'Referral_XP_Bonus', 'Points' => $referral_xp]
                    );
                    SendFirebaseNotification::dispatch(
                        $inviter_id,
                        __('notifications.Inviter_XP_Bonus.title', ['Points' => $referral_xp], $inviter_locale),
                        __('notifications.Inviter_XP_Bonus.content', ['Points' => $referral_xp], $inviter_locale),
                        'Inviter_XP_Bonus',
                        ['Type' => 'Inviter_XP_Bonus', 'Points' => $referral_xp]
                    );
                } catch (\Exception $exception) {
                }
            }

            // Send Firebase Notification for Registration
            try {
                SendFirebaseNotification::dispatch(
                    $register_response['user']->id,
                    __('notifications.Registration.title', locale: $locale),
                    __('notifications.Registration.content', locale: $locale),
                    'Registration',
                    ['Type' => 'Registration']
                );
            } catch (\Exception $exception) {
            }

            $response['token'] = $register_response['token'];

            $leaves_payload = $this->getRegisterLeavesPayload($register_response['user'], 1);
            if ($leaves_payload) {
                event(new UserScoreEvent($leaves_payload));

                // Send Firebase Notification for Leave Added
                try {
                    SendFirebaseNotification::dispatch(
                        $register_response['user']->id,
                        __('notifications.Leave_Added.title', locale: $locale),
                        __('notifications.Leave_Added.content', ['NumberOfLeaves' => 1], locale: $locale),
                        'Leave_Added',
                        ['Type' => 'Leave_Added']
                    );
                } catch (\Exception $exception) {
                }
            }

            DB::commit();

            return $this->success(
                status: true,
                message: trans('general.register_success'),
                result: $response,
                code: 201
            );
        } catch (ValidationException $e) {
            $firstMessage = collect($e->validator->errors()->all())->first();
            return $this->error(
                status: false,
                message: $firstMessage,
                code: 422
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(false, $e->getMessage());
        }
    }

    public function checkUsername(Request $request)
    {
        try {
            $request->validate([
                'username' => 'nullable|max:255',
                'email' => 'nullable|email|max:255'
            ]);
            if ($request->filled('username')) {
                $user = User::where('username', $request->username)->exists();
                if ($user) {
                    return $this->error(status: false, message: trans('general.username_exist'), code: 400);
                }
            }
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->exists();
                if ($user) {
                    return $this->error(status: false, message: trans('general.email_exist'), code: 400);
                }
            }
            $availableFields = [];
            if ($request->filled('username')) {
                $availableFields[] = 'username';
            }
            if ($request->filled('email')) {
                $availableFields[] = 'email';
            }
            $message = ucfirst(implode(' and ', $availableFields)) . ' is available.';
            return $this->success(true, $message);
        } catch (ValidationException $e) {
            $firstMessage = collect($e->validator->errors()->all())->first();
            return $this->error(
                status: false,
                message: $firstMessage,
                code: 422
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(false, $e->getMessage());
        }
    }

    public function getCitiesList(Request $request)
    {
        try {
            $search = $request['search'];
            $france = Country::where('name', 'France')->first();
            $country_id = $france->id;
            $query = City::query();
            $query = $query->join('states', 'cities.state_id', '=', 'states.id')
                ->join('countries', 'states.country_id', '=', 'countries.id');
            $query = $query
                ->where('countries.id', $country_id)
                ->select('cities.name')
                ->orderBy('cities.name', 'ASC');
            if ($search) {
                $query = $query->where('cities.name', 'LIKE', "%$search%")->limit(10);
            }
            $cities = $query->get();
            return $this->success(true, trans('general.cities_fetched'), $cities);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getSessionsDurationList()
    {
        try {
            $list = SessionTimeDuration::select('id', 'title', 'duration', 'category')->get();
            if ($list->isEmpty()) {
                return $this->error(false, trans('general.no_sessions_time_duration'));
            }
            return $this->success(true, trans('general.sessions_time_duration_fetched'), $list);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->login_service->authenticate($request->only(['email', 'password']));
            // Update User FCM Token
            if ($request->fcm_token) {
                $this->updateFcmToken(userId: $result['user']->id, token: $request->fcm_token);
            }
            $this->user_service->updateLastActiveAt($result['user']);

            $user_details_response = $this->getUserDetails($request, false, $result['user']);
            $response = $user_details_response;
            $response['token'] = $result['token'];
            return $this->success(status: true, message: trans('general.user_logged_in'), result: $response);
        } catch (ValidationException $e) {
            $status_code = $e->status ?? 422;
            return $this->error(status: false, message: $e->getMessage(), code: $status_code);
        }
    }

    public function logout()
    {
        try {
            $userId = Auth::id();
            Auth::user()->currentAccessToken()->delete();
            $this->deleteFcmToken(userId: $userId);
            return $this->success(status: true, message: trans('general.user_logged_out'));
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $result = $this->forgot_password_service->sendOTP($request->email);
            if (!$result['success']) {
                return $this->error(status: false, message: $result['message'], code: 400);
            }
            return $this->success(status: true, message: $result['message']);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $otpType = $request->type == 'email' ? 'email_verification' : 'forgot_password';
            $result = $this->otp_verification_Service->verifyOtp($request->email, $request->otp, $otpType);
            if (!$result['success']) {
                return $this->error(status: false, message: $result['message'], code: 400);
            }

            // Get user by email
            $user = User::where('email', $request->email)->first();

            // Update User FCM Token if provided
            if ($request->fcm_token) {
                $this->updateFcmToken(userId: $user->id, token: $request->fcm_token);
            }

            // Create new token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            // Get user details with all related data
            $user_details_response = $this->getUserDetails($request, false, $user);
            $response = $user_details_response;
            $response['token'] = $token;

            return $this->success(status: true, message: $result['message'], result: $response);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        try {
            $otpType = $request->type == 'email' ? 'email_verification' : 'forgot_password';
            $result = $this->resend_otp_service->reSendOTP($request->email, $otpType);
            if (!$result['success']) {
                return $this->error(status: false, message: $result['message'], code: 400);
            }
            return $this->success(status: true, message: $result['message']);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $result = $this->reset_password_service->resetPassword($request->email, $request->password);
            if (!$result['success']) {
                return $this->error(status: false, message: $result['message'], code: 400);
            }
            return $this->success(status: true, message: $result['message']);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getLanaguagesList()
    {
        try {
            $languages = Language::select('id', 'label', 'slug', 'flag')->get();
            return $this->success(true, trans('general.lanaguages_fetched'), $languages);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function changeLanguage(Request $request)
    {
        try {
            $request->validate([
                'language_id' => 'required|exists:languages,id'
            ]);
            $user = Auth::user();
            $this->user_service->changeLanguage($request->all(), $user);
            $response = $this->getUserDetails($request, false, $user);
            return $this->success(true, trans('general.language_updated'), $response);
        } catch (ValidationException $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 422);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function assignUserMode(Request $request)
    {
        try {
            $request->validate([
                'user_mode_id' => 'required|exists:modes,id',
                'company_id' => 'nullable|exists:companies,id',
                'company_department_id' => 'nullable|exists:company_departments,id',
                'join_token' => 'nullable|string',
            ]);
            $user = Auth::user();

            $data = $request->all();

            // Store the token ID for accurate tracking/idempotency, but DO NOT increment registration_count
            // per user requirement (only signups count).
            if ($request->has('join_token') && $request->join_token) {
                $joinToken = \App\Models\CompanyJoinToken::where('token', $request->join_token)->first();
                if ($joinToken) {
                    $data['join_token_id'] = $joinToken->id;
                }
            }

            $response = $this->user_service->assignUserMode($data,  $user);

            if (isset($request['company_id'])) {
                $data = [$request->company_id => []];

                if (!empty($request['company_department_id'])) {
                    $data[$request->company_id]['company_department_id'] = $request['company_department_id'];
                }

                $user->companies()->syncWithoutDetaching($data);
            }

            if ($response['success'] === false) {
                return $this->error(false, $response['message'], code: 427);
            }

            // Reconcile orphan points into the newly assigned company/mode campaign
            $active_campaign = $user->isCitizen() ? $this->getCitizenCampaign() : $this->getActiveCampanign($user->company_id);
            if ($active_campaign) {
                $this->user_score_service->reconcileOrphanPoints($user->id, $active_campaign->id, $user->company_id, $user->company_department_id);
            }

            $response = $this->getUserDetails($request, false, $user);
            return $this->success(true, trans('general.user_mode'), $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getCompanyDepartments($company_id)
    {
        try {
            $departments_list = $this->company_service->getDepartments($company_id);
            return $this->success(true, trans('general.company_departments_fetched'), $departments_list);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function modesList()
    {
        try {
            $modes = Mode::all();
            $response = ModeResource::collection($modes);
            return $this->success(status: true, result: $response);
        } catch (\Throwable $e) {
            return $this->error(status: false, message: $e->getMessage());
        }
    }

    public function getUserScores(Request $request)
    {
        try {
            $query_params = $request->all();
            $user = Auth::user();
            $response = $this->user_score_service->getUserScoresAndLeaves($query_params, $user);
            return $this->success(status: true, result: $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getUserDetails(Request $request, $is_json_response = true, $user_object = null)
    {
        try {
            DB::beginTransaction();

            if (Auth::check()) {
                $user = Auth::user();
            } else {
                $user = User::find($user_object->id);
            }
            $stats = $this->getDetailedUserStats($user);

            // Reconcile orphan points whenever user stats are fetched (ensures points from "no-campaign" periods are pulled in)
            $current_campaign = $user->isCitizen() ? $this->getCitizenCampaign() : $this->getActiveCampanign($user->company_id);
            if ($current_campaign) {
                $this->user_score_service->reconcileOrphanPoints($user->id, $current_campaign->id, $user->company_id, $user->company_department_id);
                // Refresh stats after reconciliation to show updated points
                $stats = $this->getDetailedUserStats($user);
            }

            $response['user'] = new UserResource(
                $stats['user'],
                $stats['carbon_footprint_service'],
                $stats['last_attempted_step'],
                $stats['level'],
                $stats['user_leaves'],
                $stats['ranking'],
                $stats['user_transaction_service']
            );

            DB::commit();

            if (!$is_json_response) {
                return $response;
            }
            return $this->success(true, trans('general.user_details_fetched'), $response);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(false, $e->getMessage());
        }
    }

    public function getUserLevelDetails()
    {
        try {
            $user = Auth::user();
            $current_campaign_season = null;
            if ($user->isCitizen()) {
                $current_campaign_season = CampaignsSeason::where('company_id', null)->where('status', 'active')->first();
            } else {
                $current_campaign_season = CampaignsSeason::where('company_id', $user->company_id)->where('status', 'active')->first();
            }
            $response = $this->user_score_service->getUserLevelDetails($current_campaign_season, $user);
            return $this->success(true, trans('general.user_details_fetched'), $response);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function deleteAccount()
    {
        try {
            $user = Auth::user();
            $user->tokens()->delete();
            $user->delete();
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
        return $this->success(status: true, message: trans('general.delete_account'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'image' => 'nullable|mimes:png,jpg',
                'dob' => 'nullable|date'
            ]);
            $user = Auth::user();
            $response = $this->user_service->updateProfile($request, $user);
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
        $user_response = $this->getUserDetails($request, false, $user);
        return $this->success(status: true, message: $response['message'], result: $user_response);
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'old_password' => 'required|different:password',
                'password' => 'required|confirmed'
            ]);
            $user = Auth::user();
            $response = $this->user_service->updateProfile(request: $request, user: $user);
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage(), code: 400);
        }
        $user_response = $this->getUserDetails($request, false, $user);
        return $this->success(status: true, message: trans('general.change_password'), result: $user_response);
    }

    public function updateActivity()
    {
        try {
            $user = Auth::user();
            $this->user_service->updateLastActiveAt($user);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
        return $this->success(status: true, message: 'Activity updated successfully.');
    }
}
