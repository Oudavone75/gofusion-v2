<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\Quiz;
use App\Services\CampaignSeasonService;
use App\Services\GoSessionService;
use App\Services\QuizService;
use Illuminate\Http\Request;
use App\Traits\ExcelImport;
use Carbon\Carbon;

class QuizController extends Controller
{
    use ExcelImport;
    public function __construct(private QuizService $quiz_service) {}

    public function index(Request $request)
    {
        $quizzes = $this->quiz_service->getQuizzes($request->company_id);
        $companies = $this->quiz_service->getCompanies();
        return view('admin.quiz.index', compact('quizzes', 'companies'));
    }

    public function create()
    {
        $companies = $this->quiz_service->getCompanies();
        $themes = $this->quiz_service->getThemes();
        return view('admin.quiz.create', compact('companies', 'themes'));
    }


    public function store(StoreQuizRequest $request)
    {
        try {
            $this->quiz_service->createQuiz($request->validated(), 'admin');
            $type = $request->input('type', 'season');
            return response()->json([
                'redirect' => route('admin.quiz.index', ['type' => $type]),
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
        return view('admin.quiz.view', compact('quiz'));
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

            return view('admin.quiz.edit', compact('quiz', 'companies', 'campaigns', 'sessions', 'themes'));
        } catch (\Exception $e) {
            return redirect()->route('admin.quiz.index')->with('error', $e->getMessage());
        }
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        try {
            $this->quiz_service->updateQuiz($quiz, $request->all(), 'admin');
            $type = $request->input('type', 'season');
            return response()->json([
                'redirect' => route('admin.quiz.index',['type' => $type]),
                'message' => 'Quiz updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $number_of_questions = $request->input('num_questions');
            $from = $request->input('from');
            $to = $request->input('to');

            $spreadsheet = $this->load(file: $request->file('file'));

            $rows = $this->getQuizSheet(spreadsheet: $spreadsheet);
            $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);

            $filteredQuestions = $this->filterQuestions(
                rows: $rows,
                header: $header,
                from: $from,
                to: $to,
                limit: $number_of_questions
            );
            return response()->json([
                'status' => true,
                'data' => $filteredQuestions,
                'message' => 'Quiz imported successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function attemptedUsers(Request $request, Quiz $quiz, $type)
    {
        $users = $this->quiz_service->getQuizWithAttemptedUsers($quiz->id, $request->search);
        if ($request->ajax()) {
            return view('admin.quiz.attempted-users', compact('quiz', 'users', 'type'))->render();
        }
        return view('admin.quiz.attempted-users', compact('quiz', 'users', 'type'));
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
