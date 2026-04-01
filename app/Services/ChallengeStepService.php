<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\ChallengeCategory;
use App\Models\ChallengeStep;
use App\Models\Challenge;
use App\Http\Resources\ThemeChallengeResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Traits\AppCommonFunction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChallengeStepService
{
    use AppCommonFunction;

    public function getThemes(): array
    {
        $themes = Theme::query()
            ->select('id', 'french_name as name', 'slug', 'image')
            ->get()
            ->map(function ($theme) {
                // Extract number from image filename
                $odd_index = null;
                if (preg_match('/\/(\d+)_/', $theme->image, $matches)) {
                    $odd_index = (int) $matches[1];
                }

                return [
                    'id'        => $theme->id,
                    'name'      => $theme->name,
                    'slug'      => $theme->slug,
                    'image'     => $theme->image,
                    'odd_index' => $odd_index,
                ];
            });

        if (!$themes) {
            return ['success' => false, 'message' => trans('general.no_themes_available'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.themes_fetched'), 'data' => $themes];
    }

    public function getCategories(): array
    {
        $categories = ChallengeCategory::query()->select('id', 'name', 'slug')->get();

        if (!$categories) {
            return ['success' => false, 'message' => trans('general.challenge_categories_not_found'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.challenge_categories_fetched'), 'data' => $categories];
    }

    public function getThemeChallengesListing($mode, $theme_id): array
    {
        $query = ChallengeStep::query();

        if (filled($theme_id)) {
            $query->where('theme_id', $theme_id);
        }

        if (filled($mode)) {
            $query->when($mode === 'citizen', function ($q) {
                $q->whereNull('company_id');
            }, function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
            if ($mode === 'employee' && Auth::user()->company_department_id) {
                $query->where(function ($q) {
                    $q->whereHas('departments', function ($sq) {
                        $sq->where('company_department_id', Auth::user()->company_department_id);
                    })->orWhere(function ($sq) {
                        $sq->whereDoesntHave('departments')
                            ->where('department_id', Auth::user()->company_department_id);
                    });
                });
            }
        }

        $query = $query->where('status', 'approved')->with(['theme', 'category', 'company'])->orderByDesc('created_at')->orderByDesc('updated_at');
        Log::info('Theme Challenges Query: ' . $query->toSql(), $query->getBindings());

        $theme_challenges = $query->get();

        if ($theme_challenges->isEmpty()) {
            return ['success' => false, 'message' => trans('general.theme_challenges_not_found'), 'data' => []];
        }

        return [
            'success' => true,
            'message' => trans('general.theme_challenges_fetched'),
            'data' => ThemeChallengeResource::collection($theme_challenges)
        ];
    }

    public function getThemeChallengeDetail($id): array
    {
        $challenge_detail = ChallengeStep::with(['theme', 'category', 'company'])->find($id);
        if (!$challenge_detail) {
            return ['success' => false, 'message' => trans('general.theme_challenge_not_found'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.theme_challenge_fetched'), 'data' => new ThemeChallengeResource($challenge_detail)];
    }

    public function uploadChallengeImage($request): array
    {
        $filename = uploadFile($request->file('image'), 'public', 'challenges');
        $image_url = asset('storage/challenges/' . $filename);

        if (!$image_url) {
            return ['success' => false, 'message' => trans('general.challenge_image_not_found'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.image_uploaded'), 'data' => $image_url];
    }

    public function validateChallenge($request): array
    {
        try {
            $challenge = ChallengeStep::find($request->challenge_id);

            if (!$challenge) {
                return [
                    'status' => false,
                    'message' => trans('general.step_not_found'),
                    'data' => [],
                ];
            }

            $already_attempted = $challenge->challengePoints()->where('user_id', Auth::id())->exists();

            if ($already_attempted) {
                return [
                    'status' => false,
                    'message' => trans('general.already_attempted'),
                    'data' => [],
                ];
            }

            $validation_result = null;

            if ($challenge->mode == 'photo') {
                $payload = [
                    'image_url' => $request->image_url,
                    'challenge_id' => $request->challenge_id,
                    'instructions' => $challenge->guideline_text,
                ];
                try {
                    $response = Http::timeout(60)->retry(2, 1000)->post(
                        config('services.photo_validation.url') . '?api-key=' . config('services.photo_validation.api_key'),
                        $payload
                    );
                } catch (\Exception $e) {
                    return [
                        'status' => false,
                        'message' => trans('general.challenge_validation_failed'),
                        'data' => [],
                    ];
                }
                $validation_result = $response->json();
            }
            if ($validation_result == true || $validation_result == false || $validation_result == null) {
                $valid_modes = ['video', 'checkbox'];

                if ($validation_result === true || in_array($challenge->mode, $valid_modes, true)) {
                    $points_awarded = $challenge->attempted_points;
                } else {
                    $points_awarded = 0;
                }
                $company_id = $challenge?->company_id ?? null;
                if ($company_id) {
                    $campaignSeason = $this->getActiveCampanign($company_id);
                } else {
                    $campaignSeason = $this->getCitizenCampaign();
                }
                $user = Auth::user();
                $this->addOrUpdateChallengePoints($company_id, $campaignSeason, $points_awarded, $user);
                DB::table('challenge_points')->insert([
                    'challenge_step_id' => $challenge->id,
                    'user_id' => $user->id,
                    'points' => (int)$points_awarded,
                ]);

                $challenge->comment = $request->comment ?? null;
                $challenge->save();

                return [
                    'status' => true,
                    'message' => trans('general.challenge_validated'),
                    'data' => [
                        'earned_points' => $points_awarded
                    ],
                ];
            }

            return [
                'status' => false,
                'message' => trans('general.image_validation_failed'),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    public function getAllChallengesSteps($company_id = null)
    {
        $query = Challenge::with('goSessionStep')->withCount('attempts');
        if ($company_id) {
            $query->where('company_id', $company_id);
        } else {
            $query->whereHas('goSessionStep.goSession.campaignSeason', function ($q) {
                $companyCheck = activeCampaignSeasonFilter() === 'campaign' ? 'whereNotNull' : 'whereNull';
                $q->{$companyCheck}('company_id');
            });
        }

        return $this->getPaginatedData($query);
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function create($request)
    {
        return Challenge::create($request);
    }

    public function update($challenge_step, array $data)
    {
        $challenge_step->update($data);
        return $challenge_step;
    }

    public function delete($id)
    {
        $challenge_step = Challenge::findOrFail($id);
        return $challenge_step->delete();
    }

    public function isChallengeStepExists($id)
    {
        return ChallengeStep::where('go_session_step_id', $id)->exists();
    }

    public function getChallengeStepDetails($id)
    {
        return Challenge::with('goSessionStep')->findOrFail($id);
    }

    public function getGoSessionStepId($session)
    {
        return $this->getStepPosition($session, config('constants.POSITION.FOURTH'))->id;
    }

    public function isChallengeStepGuideLineExists($go_session_step_id, $edit_id = null)
    {
        $query = Challenge::where('go_session_step_id', $go_session_step_id);
        if ($edit_id) {
            $query->where('id', '!=', $edit_id);
        }
        return $query->exists();
    }

    public function getAttemptedUsers($go_session_step_id)
    {
        return $this->getStepAttemptedUsers('challenge_attempts', 'go_session_step_id', $go_session_step_id);
    }

    public function attemptedUserDetails($user_id)
    {
        $query = User::query();

        if ($user_id) {
            $query->with([
                'company',
                'department'
            ]);
        }

        return $query->findOrFail($user_id);
    }

    public function accept($user_id, $go_session_step_id, $points, $guideline_text, $description)
    {
        $challenge_step = ChallengeStep::where('user_id', $user_id)->where('go_session_step_id', $go_session_step_id)->first();
        $challenge_step->update([
            'status' => 'approved',
            'attempted_points' => $points,
            'guideline_text' => $guideline_text,
            'description' => $description
        ]);
        return $challenge_step;
    }

    public function reject($user_id, $go_session_step_id)
    {
        $challenge_step = ChallengeStep::where('user_id', $user_id)->where('go_session_step_id', $go_session_step_id)->first();
        $challenge_step->update(['status' => 'rejected', 'attempted_points' => 0]);
        return $challenge_step;
    }

    public function getInspirationChallengeDetails($user_id, $go_session_step_id)
    {
        return ChallengeStep::where('user_id', $user_id)->where('go_session_step_id', $go_session_step_id)->first();
    }

    public function acceptInspirationChallenge($challenge_id, $points, $guideline_text)
    {
        $challenge_step = ChallengeStep::where('id', $challenge_id)->whereNull('go_session_step_id')->first();
        $challenge_step->update(['status' => 'approved', 'attempted_points' => $points, 'guideline_text' => $guideline_text]);
        return $challenge_step;
    }

    public function rejectInspirationChallenge($challenge_id)
    {
        $challenge_step = ChallengeStep::where('id', $challenge_id)->whereNull('go_session_step_id')->first();
        $challenge_step->update(['status' => 'rejected', 'attempted_points' => 0]);
        return $challenge_step;
    }

    public function getInspirationChallengeDetailsById($challenge_id)
    {
        return ChallengeStep::where('id', $challenge_id)->whereNull('go_session_step_id')->first();
    }
}
