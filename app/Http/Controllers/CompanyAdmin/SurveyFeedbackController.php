<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\SurvayFeedback;
use App\Services\SurveyFeedbackService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SurveyFeedbackRequest;
use App\Http\Requests\Admin\UpdateSurveyFeedbackRequest;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SurveyFeedbackController extends Controller
{
    use AppCommonFunction;
    public $company_admin_survey_feedback_service;
    protected $views_directory = 'company_admin.session-steps.survey-feedback.';
    protected $route_directory = 'company_admin.steps.survey-feedback.';

    public function __construct(SurveyFeedbackService $company_admin_survey_feedback_service)
    {
        $this->company_admin_survey_feedback_service = $company_admin_survey_feedback_service;
    }


    public function index()
    {
        $company_id = Auth::user()->company_id;
        $survey_feedbacks = $this->company_admin_survey_feedback_service->getSurveyFeedbacks($company_id);
        return view($this->views_directory . 'index', compact('survey_feedbacks'));
    }

    public function create()
    {
        $company_id = Auth::user()->company_id;
        $campaigns = $this->getCompanyCampaigns($company_id);

        return view($this->views_directory . 'create', compact('campaigns'));
    }

    public function store(SurveyFeedbackRequest $request)
    {
        try {
            $request->merge(['company' => Auth::user()->company_id]);
            $this->company_admin_survey_feedback_service->createSurveyFeedback($request->all(), 'web');
            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'Survey created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function view(SurvayFeedback $survey_feedback)
    {
        $survey_feedback = $this->company_admin_survey_feedback_service->getSurveyFeedbackWithRelations($survey_feedback->id);
        return view($this->views_directory . 'view', compact('survey_feedback'));
    }

    public function edit($survey_feedback)
    {
        $survey_feedback = $this->company_admin_survey_feedback_service->getSurveyFeedbackWithRelations($survey_feedback);

        // Check if survey_feedback has attempts
        if ($survey_feedback->attempts()->exists()) {
            return redirect()->back()->with('error', 'Cannot edit a survey that has attempts');
        }

        return view($this->views_directory . 'edit', compact('survey_feedback'));
    }

    public function update(UpdateSurveyFeedbackRequest $request, SurvayFeedback $survey_feedback)
    {
        try {
            $this->company_admin_survey_feedback_service->updateSurveyFeedback($survey_feedback, $request->all(), 'web');

            return response()->json([
                'redirect' => route($this->route_directory . 'index'),
                'message' => 'Survey updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(SurvayFeedback $survey_feedback)
    {
        try {
            $this->company_admin_survey_feedback_service->deleteFeedback($survey_feedback->id);
            return response()->json(['success' => true, 'message' => 'Survey deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function attemptedUsers(SurvayFeedback $survey_feedback)
    {
        $users = $this->company_admin_survey_feedback_service->getSurveyFeedbackWithAttemptedUsers($survey_feedback->id);
        $go_session_step_id = $survey_feedback->go_session_step_id;
        return view('company_admin.session-steps.survey-feedback.attempted-users', compact('survey_feedback', 'users', 'go_session_step_id'));
    }

    public function export(Request $request, $id)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
                'type'       => 'required|in:excel,csv'
            ]);

            $start_date = $request->start_date;
            $end_date   = $request->end_date ?? $start_date;

            $extension = $request->type === 'csv' ? 'csv' : 'xlsx';

            if ($start_date && $end_date) {
                $file_name = 'survey_feedback_users_'
                    . Carbon::parse($start_date)->format('Y-m-d')
                    . '_to_'
                    . Carbon::parse($end_date)->format('Y-m-d')
                    . '.' . $extension;
            } else {
                $file_name = 'survey_feedback_users_all.' . $extension;
            }

            return $this->company_admin_survey_feedback_service->export(
                $start_date,
                $end_date,
                $file_name,
                $id,
                $request->type
            );

        } catch (\Exception $e) {
            \Log::error('Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Something went wrong while exporting. Please try again.');
        }
    }
}
