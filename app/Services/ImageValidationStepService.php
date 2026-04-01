<?php

namespace App\Services;

use App\Events\StoreCompleteUserSessionEvent;
use App\Models\ImageSubmissionGuideline;
use App\Models\ImageSubmissionStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\UserProgressEvent;
use App\Events\UserScoreEvent;
use App\Traits\AppCommonFunction;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;



class ImageValidationStepService
{
    use AppCommonFunction;

    public function __construct(
        private VideoPerformanceService $videoPerformanceService,
        private CampaignPerformanceService $campaignPerformanceService
    ) {}

    public function getImageStepDetails(array $request): array
    {
        $image_step_details = ImageSubmissionGuideline::query()->select('id', 'go_session_step_id', 'title', 'description', 'mode', 'guideline_file', 'image_path', 'guideline_text', 'points', 'video_url', 'keywords')->where('go_session_step_id', $request['go_session_step_id'])->latest()->first();

        if (!$image_step_details) {
            return ['success' => false, 'message' => trans('general.step_not_found'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.image_step_details_fetched'), 'data' => $image_step_details];
    }

    public function uploadStepImage($request): array
    {
        $filename = uploadFile($request->file('image'), 'public', 'StepImages');
        $image_url = asset('storage/StepImages/' . $filename);

        if (!$image_url) {
            return ['success' => false, 'message' => trans('general.image_not_uploaded'), 'data' => []];
        }
        return ['success' => true, 'message' => trans('general.image_uploaded'), 'data' => $image_url];
    }

    public function validateStepImage($request): array
    {
        try {
            $user = Auth::user();
            $validation_result = null;
            $image_submission_guideline = ImageSubmissionGuideline::with(['goSessionStep.goSession.campaignSeason'])->where('go_session_step_id', $request->go_session_step_id)->first();
            if (!$image_submission_guideline) {
                return [
                    'success' => false,
                    'message' => trans('general.step_not_found'),
                    'data' => [],
                ];
            }

            $campaign_season_id = $image_submission_guideline->goSessionStep->goSession->campaignSeason->id;
            $user = Auth::user();
            $already_attempted = ImageSubmissionStep::where('user_id', $user->id)
                ->where('go_session_step_id', $request->go_session_step_id)
                ->exists();

            if ($already_attempted) {
                return [
                    'success' => false,
                    'message' => trans('general.already_attempted'),
                    'data' => [],
                ];
            }
            $validation_result = null;
            if ($image_submission_guideline->mode == 'photo') {
                $payload = [
                    'image_url' => $request->image_url,
                    'challenge_id' => $request->go_session_step_id,
                    'instructions' => $image_submission_guideline->guideline_text,
                ];
                try {
                    $response = Http::timeout(60)->retry(2, 1000)->post(
                        config('services.photo_validation.url') . '?api-key=' . config('services.photo_validation.api_key'),
                        $payload
                    );
                } catch (\Throwable $th) {
                    return [
                        'success' => false,
                        'message' => trans('general.image_validation_failed'),
                        'data' => [],
                    ];
                }
                $validation_result = $response->json();
            }
            if ($validation_result == true || $validation_result == false || $validation_result == null) {
                DB::beginTransaction();

                $valid_modes = ['video', 'checkbox'];

                $points_awarded = 0;
                $percentage = 0.0;
                $totalPoints = $image_submission_guideline->points ?? 0;
                $matchedConcepts = null;

                if ($validation_result === true || $image_submission_guideline->mode === 'checkbox') {
                    $points_awarded = $totalPoints;
                    $percentage = 100.0;
                } elseif ($image_submission_guideline->mode === 'video') {
                    $keywords = $image_submission_guideline->keywords ?? [];
                    $keywordsArray = is_array($keywords) ? $keywords : [];

                    $performance = $this->videoPerformanceService->evaluateUnderstanding(
                        $request->comment ?? '',
                        $keywordsArray,
                        (int)$totalPoints
                    );

                    $points_awarded = $performance['earned_points'];
                    $percentage = $performance['percentage'];
                    $matchedConcepts = json_encode($performance['matched_concepts']);
                }

                DB::table('image_submission_steps')->insert([
                    'file_name' => $request?->image_url ?? $image_submission_guideline->image_path,
                    'go_session_step_id' => $request->go_session_step_id,
                    'user_id' => $user->id,
                    'points' => (int)$points_awarded,
                    'total_points' => (int)$totalPoints,
                    'percentage' => $percentage,
                    'matched_concepts' => $matchedConcepts,
                    'comment' => $request->comment ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update global performance if video
                if ($image_submission_guideline->mode === 'video' && isset($campaign_season_id)) {
                    $this->campaignPerformanceService->updateVideoPerformance($user->id, $campaign_season_id, $percentage);
                }
                $progress_payload = $this->getUserProgressPayload($request->go_session_step_id, $user, 1);
                if ($progress_payload) {
                    event(new UserProgressEvent($progress_payload));
                    event(new StoreCompleteUserSessionEvent($progress_payload));
                }

                $score_payload = $this->getUserScorePayload($request->go_session_step_id, $user, $points_awarded);
                if ($score_payload) {
                    event(new UserScoreEvent($score_payload));
                }

                $leaves_payload = $this->getUserScoreLeavesPayload($request->go_session_step_id, $user, 1);
                if ($leaves_payload) {
                    event(new UserScoreEvent($leaves_payload));
                }
                DB::commit();

                $responseData = [
                    'earned_points' => (int)$points_awarded,
                    'percentage'    => $percentage,
                    'matched_concepts' => $matchedConcepts ? json_decode($matchedConcepts, true) : null,
                ];

                $message = trans('general.image_step_validated');

                if ($image_submission_guideline->mode === 'video') {
                    $allKeywords = is_array($image_submission_guideline->keywords) ? $image_submission_guideline->keywords : [];
                    $matched = $matchedConcepts ? json_decode($matchedConcepts, true) : [];
                    $missingConcepts = array_values(array_diff($allKeywords, $matched));

                    if (empty($missingConcepts)) {
                        $message = trans('general.video_all_concepts_matched');
                        $responseData['feedback'] = trans('general.video_feedback_all_matched_with_score_and_percentage', [
                            'points' => (int)$points_awarded,
                            'percentage' => $percentage
                        ]);
                    } else {
                        $message = trans('general.video_missing_concepts', [
                            'concepts' => implode(', ', $missingConcepts),
                        ]);
                        $responseData['feedback'] = trans('general.video_feedback_missing_concepts_with_score_and_percentage', [
                            'points' => (int)$points_awarded,
                            'percentage' => $percentage,
                            'concepts' => implode(', ', $missingConcepts)
                        ]);
                        $responseData['missing_concepts'] = $missingConcepts;
                    }
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'message' => trans('general.image_validation_failed'),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    public function getAllImageSteps($company_id = null)
    {
        if ($company_id) {
            $query = ImageSubmissionGuideline::with('goSessionStep')->withCount(['attempts', 'appealingAttempts'])
                ->whereHas('goSessionStep', function ($q) use ($company_id) {
                    $q->whereHas('goSession', function ($q) use ($company_id) {
                        $q->whereHas('campaignSeason', function ($q) use ($company_id) {
                            $q->where('company_id', $company_id);
                            $q->where('end_date', '>=', date('Y-m-d'));
                        });
                    });
                });
        } else {
            $query = ImageSubmissionGuideline::with('goSessionStep')->withCount(['attempts', 'appealingAttempts'])
                ->whereHas('goSessionStep', function ($q) {
                    $q->whereHas('goSession', function ($q) {
                        $q->whereHas('campaignSeason', function ($q) {
                            $companyCheck = activeCampaignSeasonFilter() === 'campaign' ? 'whereNotNull' : 'whereNull';
                            $q->{$companyCheck}('company_id')->where('end_date', '>=', date('Y-m-d'));
                        });
                    });
                });
        }
        return $this->getPaginatedData($query);
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function create($request)
    {
        return ImageSubmissionGuideline::create($request);
    }

    public function update($image_step, array $data)
    {
        $image_step->update($data);
        return $image_step;
    }

    public function delete($id)
    {
        $image_step = ImageSubmissionGuideline::findOrFail($id);
        return $image_step->delete();
    }

    public function isImgageStepExists($id)
    {
        return ImageSubmissionStep::where('go_session_step_id', $id)->exists();
    }

    public function getImageStepDetailsById($id)
    {
        return ImageSubmissionGuideline::with('goSessionStep')->findOrFail($id);
    }

    public function getImageStepDetailsByGoSessionStepId($id)
    {
        return ImageSubmissionStep::with('goSessionStep')->where('go_session_step_id', $id)->first();
    }

    public function getGoSessionStepId($session)
    {
        return $this->getStepPosition($session, config('constants.POSITION.SECOND'))->id;
    }

    public function isImgageStepGuideLineExists($go_session_step_id, $edit_id = null)
    {
        $query = ImageSubmissionGuideline::where('go_session_step_id', $go_session_step_id);
        if ($edit_id) {
            $query->where('id', '!=', $edit_id);
        }
        return $query->exists();
    }

    public function getAttemptedUsers($id, $search = null)
    {
        return $this->getStepAttemptedUsers('image_attempts', 'go_session_step_id', $id, $search);
    }

    public function getAppealingUsers($id, $search = null)
    {
        return $this->getStepAttemptedUsers('appealing_attempts', 'go_session_step_id', $id, $search);
    }

    public function appealForManualValidate($request)
    {
        try {
            $image_submission_guideline = ImageSubmissionGuideline::where('go_session_step_id', $request->go_session_step_id)->first();

            if (!$image_submission_guideline) {
                return [
                    'success' => false,
                    'message' => trans('general.step_not_found'),
                    'data' => [],
                ];
            }

            $imageSubmissionStep = ImageSubmissionStep::where('go_session_step_id', $request->go_session_step_id)->first();
            $imageSubmissionStep->status = 'appealing';
            $imageSubmissionStep->save();
            return [
                'success' => true,
                'message' => trans('general.appeal_submitted'),
                'data' => [],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data' => [],
            ];
        }

    }

    public function getImageChallengeDetails($user_id, $go_session_step_id)
    {
        return ImageSubmissionStep::where('user_id', $user_id)->where('go_session_step_id', $go_session_step_id)->first();
    }

    public function attemptedUserDetails($user_id)
    {
        $query = User::query();

        if ($user_id) {
            $query->with([
                'company',
                'department'
            ]);
        }

        return $query->findOrFail($user_id);
    }

    public function export($start_date, $end_date, $file_name, $id, $type = 'excel')
    {
        $imageGuideline = ImageSubmissionGuideline::where('go_session_step_id', $id)->first();

        if (!$imageGuideline) {
            return redirect()->back()->with('error', 'Image step not found.');
        }

        $attemptsQuery = ImageSubmissionStep::with([
            'user.company',
            'user.department',
            'goSessionStep.goSession.campaignSeason'
        ])->orderBy('created_at', 'desc')->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->where('go_session_step_id', $id);

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
            return $this->exportCsv($attempts, $file_name, $imageGuideline);
        }

        return $this->exportExcel($attempts, $file_name, $imageGuideline);
    }

    private function exportCsv($attempts, string $file_name, $imageGuideline)
    {
        $isEmployeeMode = $imageGuideline->goSessionStep->goSession->campaignSeason->company_id !== null;

        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Campaign/Season Name',
        ];

        if ($isEmployeeMode) {
            $headers[] = 'Company Name';
            $headers[] = 'Department Name';
        }

        $headers = array_merge($headers, [
            'Total Points',
            'Submitted Image URL',
            'Image Title',
            'Description',
            'Image URL',
            'Mode',
            'AI Description',
            'Video URL',
            'Comment',
            'Concepts Expected',
            'Concepts Matched',
            'Video Score %',
            'Step Point'
        ]);

        return response()->streamDownload(function () use ($attempts, $headers, $imageGuideline, $isEmployeeMode) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($attempts as $attempt) {
                $row = [
                    $attempt->user->first_name ?? 'N/A',
                    $attempt->user->last_name ?? 'N/A',
                    $attempt->user->email ?? 'N/A',
                    $attempt->goSessionStep->goSession->campaignSeason->title ?? 'N/A',
                ];

                if ($isEmployeeMode) {
                    $row[] = $attempt->user->company->name ?? 'N/A';
                    $row[] = $attempt->user->department->name ?? 'N/A';
                }

                $expectedConcepts = is_array($imageGuideline->keywords) ? implode(', ', $imageGuideline->keywords) : 'N/A';
                $matchedConcepts = is_array($attempt->matched_concepts) ? implode(', ', $attempt->matched_concepts) : 'N/A';
                $percentage = $attempt->percentage !== null ? $attempt->percentage . '%' : 'N/A';

                $row = array_merge($row, [
                    $attempt->points ?? 0,
                    $attempt->file_name ?? 'N/A',
                    $imageGuideline->title ?? 'N/A',
                    $imageGuideline->description ?? 'N/A',
                    $imageGuideline->image_path ?? 'N/A',
                    $imageGuideline->mode ?? 'N/A',
                    $imageGuideline->guideline_text ?? 'N/A',
                    $imageGuideline->video_url ?? 'N/A',
                    $attempt->comment ?? 'N/A',
                    $imageGuideline->mode === 'video' ? $expectedConcepts : 'N/A',
                    $imageGuideline->mode === 'video' ? $matchedConcepts : 'N/A',
                    $imageGuideline->mode === 'video' ? $percentage : 'N/A',
                    $imageGuideline->points ?? 0,
                ]);

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $file_name, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportExcel($attempts, string $file_name, $imageGuideline)
    {
        $isEmployeeMode = $imageGuideline->goSessionStep->goSession->campaignSeason->company_id !== null;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Campaign/Season Name',
        ];

        if ($isEmployeeMode) {
            $headers[] = 'Company Name';
            $headers[] = 'Department Name';
        }

        $headers = array_merge($headers, [
            'Total Points',
            'Submitted Image URL',
            'Image Title',
            'Description',
            'Image URL',
            'Mode',
            'AI Description',
            'Video URL',
            'Comment',
            'Concepts Expected',
            'Concepts Matched',
            'Video Score %',
            'Step Point'
        ]);

        // Set headers
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '1', $header);
            $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }

        $row = 2;
        foreach ($attempts as $attempt) {
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->user->first_name ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->user->last_name ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->user->email ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->goSessionStep->goSession->campaignSeason->title ?? 'N/A');

            if ($isEmployeeMode) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->user->company->name ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->user->department->name ?? 'N/A');
            }

            $expectedConcepts = is_array($imageGuideline->keywords) ? implode(', ', $imageGuideline->keywords) : 'N/A';
            $matchedConcepts = is_array($attempt->matched_concepts) ? implode(', ', $attempt->matched_concepts) : 'N/A';
            $percentage = $attempt->percentage !== null ? $attempt->percentage . '%' : 'N/A';

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->points ?? 0);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->file_name ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->title ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->description ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->image_path ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->mode ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->guideline_text ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->video_url ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt->comment ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->mode === 'video' ? $expectedConcepts : 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->mode === 'video' ? $matchedConcepts : 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->mode === 'video' ? $percentage : 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $imageGuideline->points ?? 0);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $file_name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
