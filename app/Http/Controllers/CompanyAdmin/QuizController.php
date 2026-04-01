<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\Quiz;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Services\QuizService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QuizController extends Controller
{
    private $user;
    public function __construct(private QuizService $quiz_service)
    {
        $this->user  = Auth::user();
    }
    public function index()
    {
        $company_id = $this->user->company_id;
        $quizzes = $this->quiz_service->getQuizzes($company_id);
        return view('company_admin.session-steps.quiz.index', compact('quizzes'));
    }

    public function create(CampaignSeasonService $campaign_season_service)
    {
        $company_id = $this->user->company_id;
        $campaigns = $campaign_season_service->getCompanyCampaigns($company_id);
        $themes = $this->quiz_service->getThemes();
        return view('company_admin.session-steps.quiz.create', compact('campaigns', 'themes'));
    }

    public function store(StoreQuizRequest $request)
    {
        try {
            $this->quiz_service->createQuiz($request->validated(), 'web');
            return response()->json([
                'redirect' => route('company_admin.steps.quiz.index'),
                'message' => 'Quiz created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function view(Quiz $quiz)
    {
        $quiz = $this->quiz_service->getQuizWithRelations($quiz->id);
        return view('company_admin.session-steps.quiz.view', compact('quiz'));
    }
    public function delete(Quiz $quiz)
    {
        try {
            $this->quiz_service->deleteQuiz($quiz->id);
            return response()->json(['success' => true, 'message' => 'Quiz deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit(Quiz $quiz, CampaignSeasonService $campaign_season_service, GoSessionService $go_session_service)
    {
        try {
            $quiz = $this->quiz_service->getQuizWithRelations($quiz->id);

            if ($quiz->attempts()->exists()) {
                throw new \Exception('Cannot edit a quiz that has user attempts');
            }

            $companies = $this->quiz_service->getCompanies();
            $themes = $this->quiz_service->getThemes();
            $campaigns = $campaign_season_service->getCompanyCampaigns($quiz->company_id);
            $sessions = $go_session_service->getGoSessions($quiz->campaign_season_id);

            return view('company_admin.session-steps.quiz.edit', compact('quiz', 'companies', 'campaigns', 'sessions', 'themes'));
        } catch (\Exception $e) {
            return redirect()->route('company_admin.steps.quiz.index')->with('error', $e->getMessage());
        }
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        try {
            $this->quiz_service->updateQuiz($quiz, $request->all(), 'admin');
            return response()->json([
                'redirect' => route('company_admin.steps.quiz.index'),
                'message' => 'Quiz updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function attemptedUsers(Quiz $quiz)
    {
        $users = $this->quiz_service->getQuizWithAttemptedUsers($quiz->id);
        return view('company_admin.session-steps.quiz.attempted-users', compact('quiz','users'));
    }

    public function export(Request $request, $id)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
                'type'       => 'nullable|in:excel,csv',
            ]);

            $start_date = $request->start_date;
            $end_date   = $request->end_date;

            if ($start_date && !$end_date) {
                $end_date = $start_date;
            }

            $extension = $request->type === 'csv' ? 'csv' : 'xlsx';

            if ($start_date && $end_date) {
                $file_name = "quiz_users_"
                    . Carbon::parse($start_date)->format('Y-m-d')
                    . "_to_"
                    . Carbon::parse($end_date)->format('Y-m-d')
                    . '.' . $extension;
            } else {
                $file_name = "quiz_users_all_" . date('Y-m-d_H-i-s') . '.' . $extension;
            }

            return $this->quiz_service->export(
                $file_name,
                $id,
                $start_date,
                $end_date,
                $extension
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
