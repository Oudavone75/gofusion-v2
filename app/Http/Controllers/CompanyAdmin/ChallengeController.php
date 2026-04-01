<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAdmin\ChallengeRequest;
use App\Models\ChallengeCategory;
use App\Models\ChallengePoint;
use App\Models\ChallengeStep;
use App\Models\Theme;
use App\Models\CompanyDepartment;
use App\Services\ChallengeService;
use App\Services\ChallengeStepService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Traits\AppCommonFunction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChallengeController extends Controller
{
    use AppCommonFunction, ApiResponse;
    public function __construct(public ChallengeService $challenge_service, public ChallengeStepService $challenge_step_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company_id = Auth::user()->company_id;
        $query = ChallengeStep::where('company_id', $company_id)->where('status', 'approved')->withCount('challengePoints');
        $challenges = $this->challenge_service->getChallenges($query);
        $pending_challenges_count = $this->challenge_service->getPendingChallengesCount($company_id);

        return view('company_admin.inspiration-challenges.index', compact('challenges', 'pending_challenges_count'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $departments = CompanyDepartment::query()->select('id', 'name')->where('company_id', $user->company_id)->get();
        $categories = ChallengeCategory::query()->select('id', 'name')->get();
        $themes = Theme::query()->select('id', 'french_name as name')->get();
        $events = $this->getEvents();

        return view('company_admin.inspiration-challenges.create', compact('departments', 'categories', 'themes', 'events'));
    }

    public function import()
    {
        $company_id = Auth::user()->company_id;
        $themes = Theme::query()->select('id', 'french_name as name')->get();
        $departments = CompanyDepartment::query()->select('id', 'name')->where('company_id', $company_id)->get();
        return view('company_admin.inspiration-challenges.import', compact('company_id', 'themes', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChallengeRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                unset($data["image"]);
                $data['image_path'] = $this->challenge_service->handleChallenegeImage(
                    image: $request->file('image')
                ) ?? null;
            }
            $data = [...$data, 'status' => ChallengeStep::STATUS['APPROVED'], 'user_id' => Auth::id()];

            $category = $data['challenge_category_id'] == 1 ? "image" : "event";
            if ($category == "event") {
                $data = [...$data, 'title' => $data['event_name']];
                $eventData = $this->preparedEventData(validated_data: $data, format: 'event');
            }
            $challenge_step = $this->challenge_service->create(data: $data);
            if (isset($request['departments'])) {
                $challenge_step->departments()->sync($request['departments']);
            }

            if ($challenge_step && $category == "event") {
                $this->challenge_service->storeEventRelatedData(challenge_step: $challenge_step,  data: $eventData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Challenge created successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $challenge = $this->challenge_service->find($id);
        if ($challenge->company_id !== Auth::user()->company_id) {
            return redirect()->route('company_admin.inspiration-challenges.index')->with('error', 'Challenge not found.');
        }

        return view('company_admin.inspiration-challenges.view', compact('challenge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $challenge = $this->challenge_service->find(challenge_id: $id);
        if ($challenge->company_id !== Auth::user()->company_id) {
            return redirect()->route('company_admin.inspiration-challenges.index')->with('error', 'Challenge not found.');
        }

        $user = Auth::user();
        $departments = CompanyDepartment::query()->select('id', 'name')->where('company_id', $user->company_id)->get();
        $categories = ChallengeCategory::query()->select('id', 'name')->get();
        $themes = Theme::query()->select('id', 'french_name as name')->get();
        $events = $this->getEvents();

        return view('company_admin.inspiration-challenges.edit', compact('challenge', 'departments', 'categories', 'themes', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChallengeRequest $request, $id)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                unset($data["image"]);
                $data['image_path'] = $this->challenge_service->handleChallenegeImage(
                    image: $request->file('image')
                ) ?? null;
            }

            if ($data['mode'] === 'video') {
                $data['image_path'] = null;
                $data['guideline_text'] = null;
            } else if ($data['mode'] === 'photo') {
                $data['video_url'] = null;
            } else {
                $data['image_path'] = null;
                $data['guideline_text'] = null;
                $data['video_url'] = null;
            }

            $category = $data['challenge_category_id'] == 1 ? "image" : "event";
            if ($category == "event") {
                $data = [...$data, 'title' => $data['event_name']];
                $eventData = $this->preparedEventData(validated_data: $data, format: 'event');
                $data['image_path'] = null;
                $data['guideline_text'] = null;
                $data['video_url'] = null;
            }

            $challenge_step = $this->challenge_service->update(id: $id, data: $data);
            $challenge_step = $this->challenge_service->find(challenge_id: $id);
            if (isset($data['departments'])) {
                $challenge_step->departments()->sync($data['departments']);
            }

            if ($challenge_step && $category == "event") {
                $this->challenge_service->updateEventRelatedData(id: $id,  data: $eventData);
            } else {
                $this->challenge_service->deleteEventRelatedData(id: $id);
            }
            return response()->json([
                'success' => true,
                'message' => 'Challenge updated successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $is_data_exist_of_this_challenge = $this->challenge_service->checkIfChallengeHasPoints($id);
            if ($is_data_exist_of_this_challenge) {
                return response()->json([
                    'data_exist_of_this_challenge' => true
                ]);
            }
            $challenge = $this->challenge_service->find(challenge_id: $id);
            if ($challenge->company_id !== Auth::user()->company_id) {
                return redirect()->route('company_admin.inspiration-challenges.index')->with('error', 'Challenge not found.');
            }

            $this->challenge_service->delete($challenge);

            return response()->json([
                'success' => true,
                'message' => 'Challenge deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserRequests()
    {
        $company_id = Auth::user()->company_id;
        $challenges = $this->challenge_service->getPendingChallenges($company_id);
        return view('company_admin.inspiration-challenges.user-requests', compact('challenges', 'company_id'));
    }

    public function inspirationChallengeStatus($challenge_id, $status, Request $request)
    {
        try {
            if ($status == 'accept') {
                $this->challenge_step_service->acceptInspirationChallenge($challenge_id, $request->points, $request->guideline_text);
            } else {
                $this->challenge_step_service->rejectInspirationChallenge($challenge_id);
            }
            return $this->success(status: true, message: 'User challenge ' . $status . 'ed successfully!', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getInspirationChallengeDetails($challenge_id)
    {
        $inspiration_challenge = $this->challenge_step_service->getInspirationChallengeDetailsById($challenge_id);
        $user = $this->challenge_step_service->attemptedUserDetails($inspiration_challenge->user_id);
        return view('company_admin.inspiration-challenges.user-challenge-detail', compact('user', 'inspiration_challenge'));
    }

    public function getAttemptedUsersList($challenge_step_id)
    {
        $challengesPoints = ChallengePoint::with(['user.company', 'user.department', 'challengeStep'])
            ->where('challenge_step_id', $challenge_step_id)
            ->paginate(10);
        return view('company_admin.inspiration-challenges.attempted-users-list', compact('challengesPoints'));
    }

    public function export(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
            ]);

            if ($request->start_date && $request->end_date == null) {
                $request->end_date = $request->start_date;
            }

            $start_date = $request->start_date;
            $end_date   = $request->end_date;

            $extension = $request->type === 'csv' ? 'csv' : 'xlsx';

            if ($start_date && $end_date) {
                $file_name = 'user_challenges_all' .
                    Carbon::parse($start_date)->format('Y-m-d') .
                    '_to_' .
                    Carbon::parse($end_date)->format('Y-m-d') .
                    '.' . $extension;
            } else {
                $file_name = 'user_challenges_all.' . $extension;
            }

            return $this->challenge_service->export(
                $start_date,
                $end_date,
                $request->company_id,
                $file_name,
                $request->type
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
