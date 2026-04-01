<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurvayFeedback;
use App\Services\SurveyFeedbackService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SurveyFeedbackRequest;
use App\Http\Requests\Admin\UpdateSurveyFeedbackRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SurveyFeedbackController extends Controller
{
    public $admin_survey_feedback_service;

    public function __construct(SurveyFeedbackService $admin_survey_feedback_service)
    {
        $this->admin_survey_feedback_service = $admin_survey_feedback_service;
    }

    public function index(Request $request)
    {
        $survey_feedbacks = $this->admin_survey_feedback_service->getSurveyFeedbacks($request->company_id);
        $companies = $this->admin_survey_feedback_service->getCompanies();
        return view('admin.survey-feedback.index', compact('survey_feedbacks', 'companies'));
    }

    public function create()
    {
        $companies = $this->admin_survey_feedback_service->getCompanies();
        return view('admin.survey-feedback.create', compact('companies'));
    }
    public function getCompanyCampaigns($company_id)
    {
        try {
            $campaigns = $this->admin_survey_feedback_service->getCompanyCampaigns($company_id);
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getCampaignSessions($campaign_id)
    {
        try {
            $campaign_sessions = $this->admin_survey_feedback_service->getCampaignSeasonSessions($campaign_id);
            return response()->json($campaign_sessions);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function store(SurveyFeedbackRequest $request)
    {
        try {
            $this->admin_survey_feedback_service->createSurveyFeedback($request->all(), 'admin');
            return response()->json([
                'redirect' => route('admin.survey-feedback.index', ['type' => $request->type]),
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
        $survey_feedback = $this->admin_survey_feedback_service->getSurveyFeedbackWithRelations($survey_feedback->id);
        return view('admin.survey-feedback.view', compact('survey_feedback'));
    }

    public function edit($survey_feedback)
    {
        $survey_feedback = $this->admin_survey_feedback_service->getSurveyFeedbackWithRelations($survey_feedback);

        // Check if survey_feedback has attempts
        // if ($survey_feedback->attempts()->exists()) {
        //     return redirect()->back()->with('error', 'Cannot edit a survey that has attempts');
        // }

        return view('admin.survey-feedback.edit', compact('survey_feedback'));
    }

    public function update(UpdateSurveyFeedbackRequest $request, SurvayFeedback $survey_feedback)
    {
        try {
            $this->admin_survey_feedback_service->updateSurveyFeedback($survey_feedback, $request->all(), 'admin');
            return response()->json([
                'redirect' => route('admin.survey-feedback.index'),
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
            $this->admin_survey_feedback_service->deleteFeedback($survey_feedback->id);
            return response()->json(['success' => true, 'message' => 'Survey deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function attemptedUsers(Request $request, SurvayFeedback $survey_feedback, $type)
    {
        $users = $this->admin_survey_feedback_service->getSurveyFeedbackWithAttemptedUsers($survey_feedback->id, $request->search);
        $go_session_step_id = $survey_feedback->go_session_step_id;
        if ($request->ajax()) {
            return view('admin.survey-feedback.attempted-users', compact('survey_feedback', 'users', 'go_session_step_id', 'type'))->render();
        }
        return view('admin.survey-feedback.attempted-users', compact('survey_feedback', 'users', 'go_session_step_id', 'type'));
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

            return $this->admin_survey_feedback_service->export(
                $start_date,
                $end_date,
                $file_name,
                $id,
                $request->type
            );

        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Something went wrong while exporting. Please try again.');
        }
    }

}
