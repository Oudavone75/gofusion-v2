<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyAdmin\ChallengeRequest;
use App\Models\ChallengeCategory;
use App\Models\ChallengeStep;
use App\Models\Theme;
use App\Services\ChallengeService;
use App\Models\Company;
use App\Traits\AppCommonFunction;
use Carbon\Carbon;

class ChallengeController extends Controller
{
    use AppCommonFunction;
    public function __construct(public ChallengeService $challenge_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = request('status');
        $query = ChallengeStep::approved()->withCount('challengePoints');

        $challenges = $this->challenge_service->getChallenges($query, $request->company_id, $request->type);
        $pending_challenges_count = $this->challenge_service->getPendingChallengesCount();
        $companies = $this->getCompanies();

        return view('admin.inspiration-challenges.index', compact('challenges', 'pending_challenges_count', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::query()->select('id', 'name')->get();
        $categories = ChallengeCategory::query()->select('id','name')->get();
        $themes = Theme::query()->select('id','french_name as name')->get();
        $events = $this->getEvents();

        return view('admin.inspiration-challenges.create', compact('companies','categories', 'themes', 'events'));
    }
    public function import()
    {
        $companies = Company::query()->select('id', 'name')->get();
        $themes = Theme::query()->select('id','french_name as name')->get();
        return view('admin.inspiration-challenges.import', compact('companies', 'themes'));
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
            $data = [...$data,'status' => ChallengeStep::STATUS['APPROVED']];

            $category = $data['challenge_category_id'] == 1 ? "image" : "event";
            if ($category == "event") {
                $data = [...$data,'title' => $data['event_name']];
                $eventData = $this->preparedEventData(validated_data: $data,format: 'event');
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
            ],200);

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

        return view('admin.inspiration-challenges.view', compact('challenge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $challenge = $this->challenge_service->find(challenge_id: $id);
        $companies = Company::query()->select('id', 'name')->get();
        $categories = ChallengeCategory::query()->select('id','name')->get();
        $themes = Theme::query()->select('id','french_name as name')->get();
        $events = $this->getEvents();
        $departments = $this->challenge_service->getDepartmentsByCompany($challenge->company_id);

        return view('admin.inspiration-challenges.edit', compact('challenge','companies', 'departments', 'categories', 'themes','events'));
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
                $data = [...$data,'title' => $data['event_name']];
                $eventData = $this->preparedEventData(validated_data: $data,format: 'event');
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
            ],200);

        } catch (\Exception $e) {
            dd($e);
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
            $is_data_exist_of_this_challenge=$this->challenge_service->checkIfChallengeHasPoints($id);
            if($is_data_exist_of_this_challenge){
                return response()->json([
                    'data_exist_of_this_challenge' => true
                ]);
            }
            $challenge = $this->challenge_service->find(challenge_id: $id);

            $this->challenge_service->delete($challenge);
            return response()->json([
                'success' => true,
                'message' => 'Challenge deleted successfully!'
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus($id,$status)
    {
        try {
            $this->challenge_service->update(
                id: $id,
                data: [
                    'status' => $status == "approve"
                        ? ChallengeStep::STATUS['APPROVED']
                        : ChallengeStep::STATUS['REJECTED']
                ]);
            return response()->json([
                'success' => true,
                'message' => 'Challenge approved successfully!'
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserRequests(Request $request)
    {
        $challenges = $this->challenge_service->getPendingChallenges(search: $request->search);
        $companies = Company::query()->select('id', 'name')->get();

        if ($request->ajax()) {
            return view('admin.inspiration-challenges.user-requests', compact('challenges', 'companies'))->render();
        }
        return view('admin.inspiration-challenges.user-requests', compact('challenges', 'companies'));
    }

    public function getAttemptedUsersList(Request $request, $challenge_step_id)
    {
        $challengesPoints = $this->challenge_service->getChallengeAttemptedUsers($challenge_step_id, $request->search);
        if ($request->ajax()) {
            return view('admin.inspiration-challenges.attempted-users-list', compact('challengesPoints', 'challenge_step_id'))->render();
        }
        return view('admin.inspiration-challenges.attempted-users-list', compact('challengesPoints', 'challenge_step_id'));
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
