<?php

namespace App\Services;

use App\Http\Resources\SurveyFeedbackResource;
use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\SurvayFeedback;
use App\Models\SurvayFeedbackAttempt;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use App\Models\CampaignsSeason;
use App\Models\SurvayFeedbackQuestion;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\Models\User;

class SurveyFeedbackService
{
    use AppCommonFunction;
    public function __construct(private SurvayFeedback $survay_feedback, private GoSessionStep $go_session_step, private GoSession $go_session, private SurvayFeedbackAttempt $survay_feedback_attempt) {}

    public function getFeedbackByStepId($go_session_step_id)
    {
        $survay_feedback = $this->survay_feedback
            ->with('questions.options')
            ->where('go_session_step_id', $go_session_step_id)
            ->where('status', 'active')
            ->first();

        if (!$survay_feedback) {
            return [
                'status' => false,
                'message' => trans('general.step_not_found')
            ];
        }

        return new SurveyFeedbackResource($survay_feedback);
    }

    public function createSurveyStepAttempt($request = [])
    {
        $go_session_step = $this->go_session_step->where('id', $request['go_session_step_id'])->first();
        $go_session = $this->go_session->find($go_session_step->go_session_id);
        $campaign_season_id = $go_session->campaign_season_id;
        $survey_feedback = $this->survay_feedback->where('go_session_step_id', $go_session_step->id)->first();
        $survay_feedback_attempt = $this->survay_feedback_attempt->where('go_session_step_id', $go_session_step->id)
            ->where('user_id', $request['user_id'])->first();
        if ($survay_feedback_attempt) {
            return [
                'status' => false,
                'message' => trans('general.already_attempted')
            ];
        }
        $attempt = $this->survay_feedback_attempt->create([
            'go_session_step_id' => $go_session_step->id,
            'user_id' => $request['user_id'],
            'survey_feedback_id' => $request['survey_feedback_id'],
            'points' => (int)$survey_feedback->points,
        ]);

        $feedback = $request['feedback'] ?? [];
        $feedback_responses = collect($feedback)
            ->filter(fn($res) => isset($res['question_id'], $res['option_id']))
            ->map(fn($res) => [
                'survey_feedback_attempt_id' => $attempt->id,
                'question_id' => $res['question_id'],
                'option_id' => $res['option_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (!empty($feedback_responses)) {
            DB::table('survey_feedback_responses')->insert($feedback_responses);
        }

        return [
            'campaigns_season_id' => $campaign_season_id,
            'go_session_id' => $go_session ? $go_session->id : null,
            'go_session_step_id' => $go_session_step->id,
            'is_complete' => 1,
            'survey_feedback' => $survey_feedback
        ];
    }

    public function getSurveyFeedbacks($company_id = null)
    {
        $query = $this->survay_feedback->with(['goSessionSteps', 'createdBy', 'createdByAdmin'])->withCount('attempts');
        if ($company_id) {
            $query->where('company_id', $company_id);
        }else{
            $query->whereHas('goSessionSteps.goSession.campaignSeason', function ($q) {
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

    public function getCompanyCampaigns($company_id)
    {
        return CampaignsSeason::select('id', 'title')
            ->where('company_id', $company_id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();
    }

    public function getCampaignSeasonSessions($campaign_season_id)
    {
        return GoSession::select('id', 'title')
            ->where('campaign_season_id', $campaign_season_id)
            ->where('status', 'active')
            ->get();
    }

    public function createSurveyFeedback($data, $guard)
    {
        DB::beginTransaction();

        try {
            $step = GoSessionStep::where('go_session_id', $data['session'])
                ->where('position', 6)
                ->firstOrFail();
            if ($this->survay_feedback->where('go_session_step_id', $step->id)->exists()) {
                throw new \Exception(trans('general.survey_step_exists'));
            }
            $survey_feedback = $this->survay_feedback::create([
                'go_session_step_id' => $step->id,
                'company_id' => $data['company'] ?? null,
                'campaign_season_id' => $data['campaign'],
                'go_session_id' => $data['session'],
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
                'title' => '-',
                'description' => '-',
                'points' => $data['points'],
                'status' => 'active',
            ]);

            $this->createQuestions($survey_feedback, $data['questions'], $guard);
            DB::commit();
            return $survey_feedback;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function createQuestions(SurvayFeedback $survey_feedback, array $questions, $guard)
    {
        foreach ($questions as $questionData) {
            $question = $survey_feedback->questions()->create([
                'question_text' => $questionData['text'],
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
            ]);

            $this->createOptions($question, $questionData['options'], $guard);
        }
    }
    protected function createOptions(SurvayFeedbackQuestion $question, array $options, $guard)
    {
        foreach ($options as $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
            ]);
        }
    }

    public function getSurveyFeedbackWithRelations($id, $withRelations = true)
    {
        $query = SurvayFeedback::query();

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

    public function updateSurveyFeedback(SurvayFeedback $survey_feedback, $data, $guard)
    {
        DB::beginTransaction();
        try {
            // $this->validateSurveyModification($survey_feedback);
            $survey_feedback->update([
                'title' => '-',
                'description' => '-',
                'points' => $data['points'],
            ]);
            $survey_feedback->questions()->delete();
            $this->createQuestions($survey_feedback, $data['questions'], $guard);
            DB::commit();
            return $survey_feedback;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function validateSurveyModification(SurvayFeedback $survey_feedback)
    {
        if ($survey_feedback->status === 'active' && $survey_feedback->attempts()->count() > 0) {
            throw new \Exception(trans('general.survey_cannot_edit'));
        }
    }

    public function deleteFeedback($quizId)
    {
        DB::beginTransaction();
        try {
            $survey_feedback = SurvayFeedback::withCount('attempts')->findOrFail($quizId);
            $this->validateSurveyModification($survey_feedback);
            $survey_feedback->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSurveyFeedbackWithAttemptedUsers($id, $search = null)
    {
        return $this->getStepAttemptedUsers('survey_feedback_attempts','survey_feedback_id',$id, $search);
    }

    public function export($start_date, $end_date, $file_name, $id, $type = 'excel')
    {
        $attemptsQuery = SurvayFeedbackAttempt::with('user', 'surveyFeedbackResponses')
            ->where('go_session_step_id', $id);

        if ($start_date && $end_date) {
            $attemptsQuery->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        $attempts = $attemptsQuery->get();

        if ($attempts->isEmpty()) {
            return redirect()->back()->with('error', 'No users found in the selected date range.');
        }

        if ($type === 'csv') {
            return $this->exportCsv($attempts, $file_name);
        }

        return $this->exportExcel($attempts, $file_name);
    }

    private function exportCsv($attempts, string $file_name)
    {
        $headers = [
            'User ID', 'User Name', 'User Email', 'Company', 'Department',
            'Question', 'User Answer', 'Points', 'Attempted At'
        ];

        return response()->streamDownload(function () use ($attempts, $headers) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($attempts as $a) {
                foreach ($a->surveyFeedbackResponses as $response) {
                    fputcsv($handle, [
                        $a->user->id,
                        $a->user->full_name ?? 'N/A',
                        $a->user->email ?? 'N/A',
                        $a->user->company->name ?? 'N/A',
                        $a->user->department->name ?? 'N/A',
                        $response->question->question_text ?? 'N/A',
                        $response->option->option_text ?? 'N/A',
                        $a->points ?? 0,
                        "'" . $a->created_at?->format('Y-m-d H:i:s')
                    ]);
                }
            }

            fclose($handle);
        }, $file_name, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportExcel($attempts, string $file_name)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = ['A','B','C','D','E','F','G','H','I'];
        $headers = [
            'User ID', 'User Name', 'User Email', 'Company', 'Department',
            'Question', 'User Answer', 'Points', 'Attempted At'
        ];

        foreach ($headers as $i => $h) {
            $sheet->setCellValue($columns[$i] . '1', $h);
        }

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;
        foreach ($attempts as $a) {
            foreach ($a->surveyFeedbackResponses as $response) {
                $sheet->setCellValue("A$row", $a->user->id);
                $sheet->setCellValue("B$row", $a->user->full_name ?? 'N/A');
                $sheet->setCellValue("C$row", $a->user->email ?? 'N/A');
                $sheet->setCellValue("D$row", $a->user->company->name ?? 'N/A');
                $sheet->setCellValue("E$row", $a->user->department->name ?? 'N/A');
                $sheet->setCellValue("F$row", $response->question->question_text ?? 'N/A');
                $sheet->setCellValue("G$row", $response->option->option_text ?? 'N/A');
                $sheet->setCellValue("H$row", $a->points ?? 0);
                $sheet->setCellValue("I$row", $a->created_at?->format('Y-m-d H:i:s'));
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $file_name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
