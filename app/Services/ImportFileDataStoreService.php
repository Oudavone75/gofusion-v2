<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\ChallengeStep;
use App\Models\EventSubmissionGuideline;
use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\ImageSubmissionGuideline;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SpinWheel;
use App\Models\SurvayFeedback;
use App\Models\SurvayFeedbackQuestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportFileDataStoreService
{
    public function handleQuizStoreData(array $quizData)
    {
        $skippedSessions = [];
        $insertedSessions = [];
        $is_admin = Auth::guard('admin')->check();
        $userId   = $is_admin ? Auth::guard('admin')->id() : Auth::id();

        foreach ($quizData as $data) {
            DB::beginTransaction();
            try {
                $step = GoSessionStep::where('go_session_id', $data['sessionId'])
                    ->orderBy('position')
                    ->firstOrFail();

                if (Quiz::where('go_session_step_id', $step->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }

                $quiz = $this->storeQuiz($data, $step, $is_admin,$userId);
                $this->storeQuizQuestions($quiz, $data['Questions'], $is_admin,$userId);

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            } catch (\Throwable $e) {
                Log::info("quiz logs: ".$e->getMessage());
                DB::rollBack();
            }
        }
        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }

    public function storeQuiz(array $data, GoSessionStep $step, $is_admin,$userId): Quiz
    {
        return Quiz::create([
            'go_session_step_id' => $step->id,
            'company_id'         => $data['companyId'],
            'campaign_season_id' => $data['campaignId'],
            'go_session_id'      => $data['sessionId'],
            'created_by'         => !$is_admin ? $userId : null,
            'admin_id'           => $is_admin ? $userId : null,
            'title'              => $data['Title'],
            'points'             => $data['Points'],
            'quiz_type'          => 'custom',
            'num_questions'      => count($data['Questions']),
        ]);
    }

    public function storeQuizQuestions(Quiz $quiz, array $questions, $is_admin,$userId): void
    {
        $pointsPerQuestion = $quiz->points / count($questions);

        foreach ($questions as $questionData) {
            $question = $quiz->questions()->create([
                'question_text' => $questionData['Question'],
                'created_by'         => !$is_admin ? $userId : null,
                'admin_id'           => $is_admin ? $userId : null,
                'points'        => $pointsPerQuestion,
                'explanation'   => $questionData['Explanation'] ?? null,
            ]);

            $this->storeQuizQuestionOptions($question, $questionData['Options'], $is_admin,$userId);
        }
    }

    public function storeQuizQuestionOptions(QuizQuestion $question, array $options, $is_admin,$userId): void
    {
        foreach ($options as $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'is_correct'  => $option['is_correct'],
                'created_by'         => !$is_admin ? $userId : null,
                'admin_id'           => $is_admin ? $userId : null,
            ]);
        }
    }

    public function handleChallengeStoreData(array $challengeData)
    {
        $skippedSessions = [];
        $insertedSessions = [];
        foreach ($challengeData as $data) {
            DB::beginTransaction();
            try {

                $go_session_step_id = GoSessionStep::query()
                    ->select('id')
                    ->where('go_session_id', $data['sessionId'])
                    ->where('position', config('constants.POSITION.SECOND'))
                    ->first();

                if (ImageSubmissionGuideline::where('go_session_step_id', $go_session_step_id->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }

                ImageSubmissionGuideline::create([
                    'go_session_step_id' => $go_session_step_id->id,
                    'title' => $data['Title'],
                    'points' => $data['Points'],
                    'image_path' => $data['SampleImage'],
                    'mode' => $data['Mode'],
                    'guideline_text' => $data['AIDescription'],
                    'description' => $data['Description'],
                    'video_url' => $data['VideoURL'],
                    'keywords' => $data['Keywords'] ?? null,
                ]);

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }

    public function handleSpinWheelStoreData(array $spinWheelData)
    {
        $skippedSessions = [];
        $insertedSessions = [];
        foreach ($spinWheelData as $data) {
            DB::beginTransaction();
            try {
                $go_session_step = GoSessionStep::query()
                    ->where('go_session_id', $data['sessionId'])
                    ->where('position', 5)
                    ->first();
                if (SpinWheel::query()->where('go_session_step_id', $go_session_step->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }
                SpinWheel::create([
                    'go_session_step_id' => $go_session_step->id,
                    'video_url' => $data['VideoURL'],
                    'bonus_leaves' => $data['BonusLeaves'],
                    'promo_codes' => $data['PromoCode'],
                    'points' => $data['Points'],
                    'company_id' => $data['companyId']
                ]);

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }

    public function getSessionTitle($sessionIds)
    {
        return GoSession::query()->whereIn('id', $sessionIds)->pluck('title')->toArray();
    }

    public function handleFeedbackStoreData(array $feedbackData): array
    {
        $skippedSessions = [];
        $insertedSessions = [];
        $is_admin = Auth::guard('admin')->check();
        $userId   = $is_admin ? Auth::guard('admin')->id() : Auth::id();
        foreach ($feedbackData as $data) {
            DB::beginTransaction();
            try {
                $step = GoSessionStep::where('go_session_id', $data['sessionId'])
                    ->where('position', 6)
                    ->firstOrFail();

                if (SurvayFeedback::where('go_session_step_id', $step->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }

                $quiz = $this->storeFeedback($data, $step, $is_admin,$userId);
                $this->storeFeedbackQuestions($quiz, $data['Questions'], $is_admin,$userId);

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            }catch (\Exception $e) {
                DB::rollBack();
            }
        }

        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }
    public function storeFeedback(array $data, GoSessionStep $step, $is_admin,$userId): SurvayFeedback
    {
        return SurvayFeedback::create([
            'go_session_step_id' => $step->id,
            'company_id'         => $data['companyId'],
            'campaign_season_id' => $data['campaignId'],
            'go_session_id'      => $data['sessionId'],
            'created_by'         => !$is_admin ? $userId : null,
            'admin_id'           => $is_admin ? $userId : null,
            'title'              => '-',
            'description'        => '-',
            'points'             => $data['Points'],
            'status'             => 'active',
        ]);
    }
    public function storeFeedbackQuestions(SurvayFeedback $feedback, array $questions, $is_admin,$userId): void
    {
        foreach ($questions as $questionData) {
            $question = $feedback->questions()->create([
                'question_text' => $questionData['Question'],
                'created_by'         => !$is_admin ? $userId : null,
                'admin_id'           => $is_admin ? $userId : null,
            ]);

            $this->storeFeedbackQuestionOptions($question, $questionData['Options'], $is_admin,$userId);
        }
    }
    public function storeFeedbackQuestionOptions(SurvayFeedbackQuestion $question, array $options, $is_admin,$userId): void
    {
        foreach ($options as $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'created_by'         => !$is_admin ? $userId : null,
                'admin_id'           => $is_admin ? $userId : null,
            ]);
        }
    }

    public function handleEventStoreData(array $eventData)
    {
        $skippedSessions = [];
        $insertedSessions = [];
        foreach ($eventData as $data) {
            DB::beginTransaction();
            try {

                $go_session_step_id = GoSessionStep::query()
                    ->select('id')
                    ->where('go_session_id', $data['sessionId'])
                    ->where('position', config('constants.POSITION.THIRD'))
                    ->first();

                if (EventSubmissionGuideline::where('go_session_step_id', $go_session_step_id->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }

                $eventStep = EventSubmissionGuideline::create([
                    'go_session_step_id' => $go_session_step_id->id,
                    'points' => $data['Points'],
                    'image_path' => $data['SampleImage'],
                    'guideline_text' => $data['AIDescription'],
                    'description' => $data['Description']
                ]);
                if ($eventStep) {
                    $eventStep->event()->create([
                        'title'             => $data['EventName'],
                        'description'       => $data['Description'],
                        'event_type'        => $data['EventType'],
                        'location'          => $data['EventLocation'],
                        'start_date'        => $data['EventStartDate'],
                        'end_date'          => $data['EventEndDate'],
                        'status'            => 'active',
                        'short_description' => $data['AIDescription'],
                    ]);
                }

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }
    public function handleChallengeToCreateStoreData(array $challengeToCreateData)
    {
        $skippedSessions = [];
        $insertedSessions = [];
        foreach ($challengeToCreateData as $data) {
            DB::beginTransaction();
            try {

                $go_session_step_id = GoSessionStep::query()
                    ->select('id')
                    ->where('go_session_id', $data['sessionId'])
                    ->where('position', config('constants.POSITION.FOURTH'))
                    ->first();

                if (Challenge::where('go_session_step_id', $go_session_step_id->id)->exists()) {
                    $skippedSessions[] = $data['sessionId'];
                    DB::rollBack();
                    continue;
                }

                Challenge::create([
                    'go_session_step_id' => $go_session_step_id->id,
                    'points' => $data['Points'],
                    'company_id' => $data['companyId'],
                ]);

                DB::commit();
                $insertedSessions[] = $data['sessionId'];
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
        return [
            'skipped_sessions' => $this->getSessionTitle(sessionIds: $skippedSessions),
            'inserted_sessions' => $this->getSessionTitle(sessionIds: $insertedSessions),
        ];
    }

    public function storeInspirationalChallengeData(array $inspirationalChallengeData)
    {
        foreach ($inspirationalChallengeData as $data) {
            DB::beginTransaction();
            $categorySlug = $data['Category'] == "ChallengeToComplete" ? "image_uploading" : "attend_event";
            $challengeCategoryId = $categorySlug == "image_uploading" ? 1 : 2;
            try {
                $challengeStep = ChallengeStep::create([
                    "theme_id" => $data['theme_id'],
                    "company_id" => $data['company_id'],
                    // "department_id" => $data['department_id'],
                    "challenge_category_id" => $challengeCategoryId,
                    "title" => $data['Title'],
                    "description" => $data['Description'],
                    "guideline_text" => $data['AIDescription'],
                    "attempted_points" => $data['Points'],
                    "image_path" => $data['ImageURL'],
                    "video_url" => $data['VideoURL'],
                    "status" => "approved",
                    'mode' => $data['Mode'],
                ]);

                if (isset($data['departments'])) {
                    $challengeStep->departments()->sync($data['departments']);
                }

                if ($data['Category'] == "AttendEvent") {
                    if ($challengeStep) {
                        $challengeStep->event()->create([
                            'title'             => $data['EventName'],
                            'description'       => $data['Description'],
                            'event_type'        => $data['EventType'],
                            'location'          => $data['EventLocation'],
                            'start_date'        => $data['EventStartDate'],
                            'end_date'          => $data['EventEndDate'],
                            'status'            => 'active',
                            'short_description' => $data['AIDescription'],
                        ]);
                    }
                }

                DB::commit();
            }catch (\Exception $e){
                Log::info("InspirationalException: ".$e->getMessage());
                DB::rollBack();
            }
        }
    }
}
