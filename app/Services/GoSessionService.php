<?php

namespace App\Services;

use App\Http\Resources\GoSessionResource;
use App\Http\Resources\GoSessionStepResource;
use App\Http\Resources\UserResource;
use App\Models\CampaignsSeason;
use App\Models\CompleteGoSessionUser;
use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\SessionTimeDuration;
use App\Models\UserDetail;
use App\Services\GoSessionSetpService;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
use App\Traits\ExcelImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GoSessionService
{
    use AppCommonFunction, ExcelImport, ApiResponse;

    public function __construct(
        private GoSession $go_session,
        private GoSessionStep $go_session_step,
        private GoSessionSetpService $go_session_setp_service
    ) {
        $this->go_session = $go_session;
        $this->go_session_step = $go_session_step;
    }

    public function getGoSessions($campaign_season_id)
    {
        $sessions = $this->go_session->with('goSessionSteps')
            ->where('status', config('constants.STATUS.ACTIVE'))
            ->where('campaign_season_id', $campaign_season_id)
            ->get();
        if ($sessions->isEmpty()) {
            return [];
        }
        $list = GoSessionResource::collection($sessions);
        return $list;
    }

    public function fetchSessionSteps($go_session_id)
    {
        $steps = $this->go_session_step
            ->where('go_session_id', $go_session_id)
            ->whereNotIn('position', [3, 4])
            ->orderBy('position', 'ASC')
            ->get();

        return GoSessionStepResource::collection($steps);
    }

    public function getAllCompanySessions($company_id = null)
    {
        $query = $this->go_session->with('goSessionSteps');
        if ($company_id) {
            $query->whereHas('campaignSeason', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
                $q->where('end_date', '>=', date('Y-m-d'));
            });
        } else {
            $query->whereHas('campaignSeason', function ($q) {
                $companyCheck = activeCampaignSeasonFilter() === 'campaign' ? 'whereNotNull' : 'whereNull';
                $q->{$companyCheck}('company_id')
                    ->where(function ($subQuery) {
                        $subQuery->where('end_date', '>=', date('Y-m-d'))->orWhere('end_date', '>=', date('Y-m-d'));
                    });
            });
        }
        return $this->getPaginatedData($query);
    }
    public function getCompanySessionsDashboard($company_id)
    {
        $query = $this->go_session->with('goSessionSteps');
        $query->whereHas('campaignSeason', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
            $q->where('end_date', '>=', date('Y-m-d'));
        });
        // Get the latest 5 records
        return $query->latest()->limit(5)->get();
    }

    public function getCompanyCampaigns($company_id = null)
    {


        $query = CampaignsSeason::query()->select('id', 'title');
        if ($company_id) {
            $query->where('company_id', $company_id);
        }
        return $query->where('end_date', '>=', date('Y-m-d'))->get();
    }

    public function create($request, $company_id = null)
    {
        $status = config('constants.STATUS.ACTIVE');
        $go_session = $this->go_session::create([
            'campaign_season_id' => $request['campaign'],
            'title' => $request['title'],
            'status' => $status
        ]);
        $this->go_session_setp_service->create($go_session->id, $status);
        return $go_session;
    }

    public function update($session, array $data)
    {
        $session->update([
            'campaign_season_id' => $data['campaign'],
            'title' => $data['title']
        ]);

        return $session;
    }

    public function checkIfSessionHasStep($session_id)
    {
        return GoSessionStep::where('go_session_id', $session_id)->exists();
    }


    public function delete($session)
    {
        $session->delete();
        $this->go_session_setp_service->delete($session->id);
        return true;
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function getGoSessionDetails($id)
    {
        $session = $this->go_session->with(['goSessionSteps' => function ($query) {
            $query->where('position', '!=' , 3)
                ->where('position','!=', 4);
        }])->findOrFail($id);
        if (!$session) {
            return [
                'success' => false,
                'message' => trans('general.session_not_found'),
                'data' => []
            ];
        }
        $response = new GoSessionResource($session);
        return [
            'success' => true,
            'message' => trans('general.session_details_fetched'),
            'data' => $response
        ];
    }

    public function importSessions($request)
    {
        DB::beginTransaction();

        // Load spreadsheet
        $spreadsheet = $this->load(file: $request->file('file'));
        $sessions = $spreadsheet->getActiveSheet()->toArray();

        // Get existing titles for the campaign (case-insensitive)
        $existingTitles = GoSession::where('campaign_season_id', $request->input('campaign'))
            ->pluck('title')
            ->map(fn($t) => mb_strtolower(trim($t)))
            ->toArray();

        // Validate sheet against DB and within-sheet duplicates
        $sheetErrors = $this->validateSessionsSheet($sessions, $existingTitles);
        if (!empty($sheetErrors)) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Session import failed!',
                'errors'  => $sheetErrors,
            ];
        }

        // Create sessions
        foreach ($sessions as $index => $row) {
            if ($index === 0) continue; // assume header at row 0

            $title = trim($row[0] ?? '');
            if ($title === '') continue;

            $data = $request->all();
            $data['title'] = $title;

            $this->create($data);

            // add to existingTitles to prevent duplicates within the same sheet processing
            $existingTitles[] = mb_strtolower($title);
        }

        DB::commit();
    }

    private function validateSessionsSheet(array $sessions, array $existingTitles = []): array
    {
        $errors = [];

        if (empty($sessions) || count($sessions) < 2) {
            $errors[] = "The excel sheet must contain at least one session title.";
            return $errors;
        }

        $seen = []; // track titles within sheet (lowercase)
        foreach ($sessions as $index => $row) {
            if ($index === 0) continue; // skip header

            $titleRaw = $row[0] ?? null;
            $title = is_null($titleRaw) ? '' : trim((string)$titleRaw);

            $rowNumber = $index + 1; // human-readable row number

            if ($title === '') {
                $errors[] = "Row {$rowNumber}: session title is empty.";
                continue;
            }
            // Check if title is string or numeric
            if (!is_string($titleRaw) || is_numeric($titleRaw) || !is_string((string)$titleRaw)) {
                $errors[] = "Row {$rowNumber}: session title must be a string.";
                continue;
            }

            $lc = mb_strtolower($title);
            // duplicate in DB
            if (in_array($lc, $existingTitles, true)) {
                $errors[] = "Row {$rowNumber}: session title '{$title}' already exists in the selected campaign.";
                continue;
            }
            // duplicate within sheet
            if (in_array($lc, $seen, true)) {
                $errors[] = "Row {$rowNumber}: duplicate session title '{$title}' found in sheet.";
                continue;
            }

            $seen[] = $lc;
        }

        return $errors;
    }
    public function trackWeeklyProgress($campaign_season_id)
    {
        $user = auth()->user();
        if (!$user) {
            return [
                'sessions_completed' => 0,
                'weekly_goal' => 0
            ];
        }
        $user->load('userDetails.sessionTimeDuration');

        $completed_sessions = CompleteGoSessionUser::where('user_id', $user->id)
            ->where('campaigns_season_id', $campaign_season_id)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        $weeklyGoal = 0;
        if ($user->userDetails && $user->userDetails->sessionTimeDuration) {
            $weeklyGoal = $user->userDetails->sessionTimeDuration->duration;
        }

        return [
            'sessions_completed' => $completed_sessions,
            'weekly_goal' => $weeklyGoal
        ];
    }

    public function updateSessionTimeDuration($request, $user)
    {
        $user_details = UserDetail::where('user_id', $user->id)->first();

        if (!$user_details) {
            return [
                'success' => false,
                'message' => trans('general.user_details_not_found'),
                'data' => []
            ];
        }

        $user_details->session_time_duration_id = $request['session_time_duration_id'];
        $user_details->save();
        $user_details->load('sessionTimeDuration');

        $stats = $this->getDetailedUserStats($user);

        $user_resource = new UserResource(
            $stats['user'],
            $stats['carbon_footprint_service'],
            $stats['last_attempted_step'],
            $stats['level'],
            $stats['user_leaves'],
            $stats['ranking'],
            $stats['user_transaction_service']
        );

        return [
            'success' => true,
            'message' => trans('general.session_time_duration_updated'),
            'data' => [
                'user_details' => $user_details,
                'user' => $user_resource,
            ]
        ];
    }

    public function getAllSessionTimeDurations()
    {
        $durations = SessionTimeDuration::all();
        return [
            'success' => true,
            'message' => trans('general.sessions_time_duration_fetched'),
            'data' => $durations
        ];
    }
}
