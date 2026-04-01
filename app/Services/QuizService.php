<?php

namespace App\Services;

use App\Http\Resources\QuizResource;
use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use Illuminate\Support\Facades\Auth;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use App\Models\QuizResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class QuizService
{
    use AppCommonFunction;
    public function __construct(
        private Quiz $quiz,
        private QuizAttempt $quiz_attempt,
        private GoSession $go_session,
        private GoSessionStep $go_session_step,
        private CampaignPerformanceService $campaignPerformanceService
    ) {}

    public function getQuizWithQuestionAndOptions($go_session_step_id)
    {
        $quiz = $this->quiz->with('questions.options')
            ->where('go_session_step_id', $go_session_step_id)
            ->first();
        if (!$quiz) {
            return ['status' => false, "message" => trans('general.step_not_found')];
        }
        return new QuizResource($quiz);
    }

    public function validateQuizAttempt($go_session_step_id, $request)
    {
        $go_session_step = $this->go_session_step->where('id', $go_session_step_id)->first();
        $go_session = $this->go_session->find($go_session_step->go_session_id);
        $campaign_season_id = $go_session->campaign_season_id;
        $quiz = $this->quiz->where('go_session_step_id', $go_session_step_id)->first();
        if (!$quiz) {
            return ['status' => false, "message" => trans('general.step_not_found')];
        }
        $user_quiz_attempt = $this->quiz_attempt->where('user_id', $request['user_id'])->where('quiz_id', $quiz->id)->first();
        if ($user_quiz_attempt) {
            return [
                'status' => false,
                'message' => trans('general.already_attempted')
            ];
        }
        $totalPoints = QuizQuestion::where('quiz_id', $quiz->id)->sum('points');
        $earnedPoints = 0;

        foreach ($request['user_result'] ?? [] as $res) {
            if ($this->isAnswerCorrect((int)$res['question_id'], (int)$res['option_id'])) {
                $earnedPoints += QuizQuestion::where('id', $res['question_id'])->value('points') ?? 0;
            }
        }

        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $attemptNumber = $this->quiz_attempt->where('user_id', $request['user_id'])->where('quiz_id', $quiz->id)->count() + 1;

        $request['points'] = $earnedPoints;
        $request['total_points'] = $totalPoints;
        $request['percentage'] = $percentage;
        $request['attempt_number'] = $attemptNumber;
        $request['campaign_season_id'] = $campaign_season_id;

        $this->quiz_attempt::create($request);

        // Aggregate quiz performance and update global score
        $this->campaignPerformanceService->updateQuizPerformance($request['user_id'], $campaign_season_id);

        return [
            'campaigns_season_id' => $campaign_season_id,
            'go_session_id' => $go_session ? $go_session->id : null,
            'go_session_step_id' => $go_session_step_id,
            'user' => Auth::user(),
            'is_complete' => 1,
            'quiz' => $quiz,
            'earned_points' => $earnedPoints
        ];
    }

    public function storeQuizResponses($go_session_step_id, $request)
    {
        $quiz = $this->quiz->find($request['quiz_id']);
        if (!$quiz) {
            throw new \Exception(trans('general.step_not_found'));
        }
        $responses = $request['user_result'] ?? [];
        foreach ($responses as $response) {
            $response['user_answer'] = QuizQuestionOption::find($response['option_id'])->option_text ?? null;
            $response['is_correct'] = $this->isAnswerCorrect($response['question_id'], $response['option_id']);
            QuizResponse::create([
                'go_session_step_id' => $go_session_step_id,
                'user_id' => $request['user_id'],
                'quiz_id' => $quiz->id,
                'question_id' => $response['question_id'],
                'user_answer' => $response['user_answer'],
                'is_correct' => $response['is_correct'] ?? false,
            ]);
        }
        return true;
    }

    protected function isAnswerCorrect($question_id, $option_id)
    {
        $question = QuizQuestion::with('options')->find($question_id);
        if (!$question) {
            return false;
        }
        $correctOption = $question->options->firstWhere('is_correct', true);
        return $correctOption && $correctOption->id == $option_id;
    }

    public function getQuizzes($company_id = null)
    {
        $query = Quiz::query()->with(['goSessionSteps', 'createdBy', 'createdByAdmin'])->withCount('attempts');

        if ($company_id) {
            $query->where('company_id', $company_id);
        } else {
            $query->whereHas('campaignSeason', function ($q) {
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

    public function getThemes()
    {
        return $this->getAllThemes();
    }
    public function getCampaignSeasonSessions($campaign_season_id)
    {
        return GoSession::select('id', 'title')
            ->where('campaign_season_id', $campaign_season_id)
            ->where('status', 'active')
            ->get();
    }
    public function createQuiz($data, $guard)
    {
        DB::beginTransaction();
        try {
            $step = GoSessionStep::where('go_session_id', $data['session'])
                ->orderBy('position')
                ->first();
            if (!$step) {
                throw new \Exception(trans('general.step_not_found'));
            }
            if (Quiz::where('go_session_step_id', $step->id)->exists()) {
                throw new \Exception(trans('general.step_has_quiz'));
            }
            if ($guard == 'admin') {
                $companyId = $data['type'] == 'campaign' ? $data['company'] : null;
            } else {
                $companyId = Auth::guard($guard)->user()->company_id;
            }
            $quiz = Quiz::create([
                'go_session_step_id' => $step->id,
                'company_id' => $companyId,
                'campaign_season_id' => $data['campaign'],
                'go_session_id' => $data['session'],
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
                'title' => $data['title'],
                'points' => $data['points'],
                'status' => 'active',
                'quiz_type' => $data['quiz_type'],
                'theme_id' => $data['theme_id'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'num_questions' => $data['num_questions'] ?? null,
                'num_options' => $data['num_options'] ?? null,
                'ai_rules' => $data['ai_rules'] ?? null,
            ]);
            // Add questions based on quiz type
            $questions = $data['quiz_type'] === 'custom'
                ? $data['questions']
                : $data['ai_questions'];

            $this->createQuestions($quiz, $questions, $guard);
            DB::commit();
            return $quiz;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    protected function createQuestions(Quiz $quiz, array $questions, $guard)
    {
        $pointsPerQuestion = $quiz->points / count($questions);

        foreach ($questions as $questionData) {
            $question = $quiz->questions()->create([
                'question_text' => $questionData['text'],
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
                'points' => $pointsPerQuestion,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            $this->createOptions($question, $questionData['options'], $questionData['correct'], $guard);
        }
    }
    protected function createOptions(QuizQuestion $question, array $options, $correctIndex, $guard)
    {
        foreach ($options as $i => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => $i == $correctIndex,
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
            ]);
        }
    }

    public function getQuizWithRelations($id, $withRelations = true)
    {
        $query = Quiz::query();

        if ($withRelations) {
            $query->with([
                'company',
                'campaignSeason',
                'session',
                'questions.options',
                'createdBy',
                'createdByAdmin',
            ]);
        }

        return $query->findOrFail($id);
    }
    protected function validateQuizModification(Quiz $quiz)
    {
        if ($quiz->status === 'active' && $quiz->attempts()->count() > 0) {
            throw new \Exception(trans('general.quiz_cannot_edit'));
        }
    }
    public function deleteQuiz($quizId)
    {
        DB::beginTransaction();
        try {
            $quiz = Quiz::withCount('attempts')->findOrFail($quizId);
            $this->validateQuizModification($quiz);
            $quiz->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateQuiz(Quiz $quiz, $data, $guard)
    {
        DB::beginTransaction();

        try {
            $this->validateQuizModification($quiz);
            $quiz->update([
                'title' => $data['title'],
                'points' => $data['points'],
                'quiz_type' => $data['quiz_type'],
                'theme_id' => $data['theme_id'] ?? null,
                'difficulty' => $data['difficulty'] ?? null,
                'num_questions' => $data['num_questions'] ?? null,
                'num_options' => $data['num_options'] ?? null,
                'ai_rules' => $data['ai_rules'] ?? null,

            ]);
            $quiz->questions()->delete();

            $questions = $data['quiz_type'] === 'custom'
                ? $data['questions']
                : $data['ai_questions'];
            $this->createQuestions($quiz, $questions, $guard);
            DB::commit();
            return $quiz;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getQuizWithAttemptedUsers($quiz_id, $search = null)
    {
        return $this->getStepAttemptedUsers('quiz_attempts', 'quiz_id', $quiz_id, $search);
    }

    public function export(string $fileName, $quizId, $start_date, $end_date, $extension = 'xlsx')
    {
        $quiz = $this->quiz->find($quizId);
        if (!$quiz) {
            throw new \Exception(trans('general.step_not_found'));
        }

        $responses = QuizResponse::with([
                'user.company',
                'user.department',
                'quiz',
                'question.options'
            ])
            ->where('quiz_id', $quizId)
            ->when($start_date, fn($q) => $q->whereDate('created_at', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('created_at', '<=', $end_date))
            ->get();

        if ($responses->isEmpty()) {
            return redirect()->back()->with('error', 'No responses found for this quiz.');
        }

        $usersGrouped = $responses->groupBy('user_id');

        if (activeCampaignSeasonFilter() == "campaign") {
            $campaign_season = 'Campaign';
        } else {
            $campaign_season = 'Season';
        }

        if ($extension === 'csv') {
            return $this->exportCsv($fileName, $usersGrouped, $responses, $campaign_season);
        }

        return $this->exportXlsx($fileName, $usersGrouped, $responses, $campaign_season);
    }

    private function exportCsv($fileName, $usersGrouped, $responses, $campaign_season)
    {
        $headers = [
            'Quiz Title',
            'Full Name',
            $campaign_season,
            'Session',
            'Company',
            'Department',
            'Job Title',
            'Question',
            'Correct Answer',
            'User Answer',
            'Is Correct?',
            'Explanation',
        ];

        $totalQuestions = $responses->count();
        $totalCorrect   = $responses->where('is_correct', true)->count();
        $totalWrong     = $totalQuestions - $totalCorrect;
        $correctPercent = $totalQuestions ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0;
        $wrongPercent   = $totalQuestions ? round(($totalWrong / $totalQuestions) * 100, 2) : 0;

        $callback = function () use ($headers, $usersGrouped, $totalQuestions, $totalCorrect, $totalWrong, $correctPercent, $wrongPercent) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($usersGrouped as $userResponses) {
                foreach ($userResponses as $response) {
                    $user = $response->user;
                    $correctOption = $response->question->options->firstWhere('is_correct', true);

                    fputcsv($handle, [
                        $response->quiz->title ?? 'N/A',
                        $user->full_name ?? 'N/A',
                        $user->quiz_attempts[0]?->quiz?->campaignSeason?->title ?? 'N/A',
                        $response->quiz->session->title ?? 'N/A',
                        $user->company->name ?? 'N/A',
                        $user->department->name ?? 'N/A',
                        $user->job_title ?? 'N/A',
                        $response->question->question_text ?? 'N/A',
                        $correctOption->option_text ?? 'N/A',
                        $response->user_answer ?? 'N/A',
                        $response->is_correct ? 'Yes' : 'No',
                        $response->question->explanation ?? 'N/A',
                    ]);
                }
            }

            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Questions', $totalQuestions]);
            fputcsv($handle, ['Total Correct', $totalCorrect]);
            fputcsv($handle, ['Total Wrong', $totalWrong]);
            fputcsv($handle, ['Correct Percentage (%)', $correctPercent . '%']);
            fputcsv($handle, ['Wrong Percentage (%)', $wrongPercent . '%']);

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    private function exportXlsx($fileName, $usersGrouped, $responses, $campaign_season)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'Quiz Title',
            'B1' => 'Full Name',
            'C1' => $campaign_season,
            'D1' => 'Session',
            'E1' => 'Company',
            'F1' => 'Department',
            'G1' => 'Job Title',
            'H1' => 'Question',
            'I1' => 'Correct Answer',
            'J1' => 'User Answer',
            'K1' => 'Is Correct?',
            'L1' => 'Explanation',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Bold header
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Colors
        $summaryFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFBF00']
            ]
        ];

        $greenFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '03D3C7']
            ]
        ];

        $redFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'B80079']
            ]
        ];

        // Summary data
        $totalQuestions = $responses->count();
        $totalCorrect = $responses->where('is_correct', true)->count();
        $totalWrong = $totalQuestions - $totalCorrect;

        $correctPercent = $totalQuestions ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0;
        $wrongPercent = $totalQuestions ? round(($totalWrong / $totalQuestions) * 100, 2) : 0;

        // Fill rows
        $row = 2;

        foreach ($usersGrouped as $userResponses) {
            foreach ($userResponses as $response) {
                $user = $response->user;
                $correctOption = $response->question->options->firstWhere('is_correct', true);

                $sheet->setCellValue("A{$row}", $response->quiz->title ?? 'N/A');
                $sheet->setCellValue("B{$row}", $user->full_name ?? 'N/A');
                $sheet->setCellValue("C{$row}", $user->quiz_attempts[0]?->quiz?->campaignSeason?->title ?? 'N/A');
                $sheet->setCellValue("D{$row}", $response->quiz->session->title ?? 'N/A');
                $sheet->setCellValue("E{$row}", $user->company->name ?? 'N/A');
                $sheet->setCellValue("F{$row}", $user->department->name ?? 'N/A');
                $sheet->setCellValue("G{$row}", $user->job_title ?? 'N/A');
                $sheet->setCellValue("H{$row}", $response->question->question_text ?? 'N/A');
                $sheet->setCellValue("I{$row}", $correctOption->option_text ?? 'N/A');
                $sheet->setCellValue("J{$row}", $response->user_answer ?? 'N/A');
                $sheet->setCellValue("K{$row}", $response->is_correct ? 'Yes' : 'No');
                $sheet->setCellValue("L{$row}", $response->question->explanation ?? 'N/A');

                if ($response->is_correct) {
                    $sheet->getStyle("J{$row}:K{$row}")->applyFromArray($greenFill);
                } else {
                    $sheet->getStyle("J{$row}:K{$row}")->applyFromArray($redFill);
                }

                $row++;
            }
        }

        // Summary
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Summary');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->applyFromArray($summaryFill);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Questions');
        $sheet->setCellValue("B{$row}", $totalQuestions);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Correct');
        $sheet->setCellValue("B{$row}", $totalCorrect);
        $sheet->getStyle("B{$row}")->applyFromArray($greenFill);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Wrong');
        $sheet->setCellValue("B{$row}", $totalWrong);
        $sheet->getStyle("B{$row}")->applyFromArray($redFill);
        $row++;

        $sheet->setCellValue("A{$row}", 'Correct Percentage (%)');
        $sheet->setCellValue("B{$row}", $correctPercent . '%');
        $sheet->getStyle("B{$row}")->applyFromArray($greenFill);
        $row++;

        $sheet->setCellValue("A{$row}", 'Wrong Percentage (%)');
        $sheet->setCellValue("B{$row}", $wrongPercent . '%');
        $sheet->getStyle("B{$row}")->applyFromArray($redFill);

        // Download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
