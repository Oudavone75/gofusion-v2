<?php

namespace App\Services;

use App\Models\CampaignsSeason;
use App\Models\CampaignUserPerformance;
use App\Models\ImageSubmissionStep;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerformanceExportService
{
    private const CHUNK_SIZE = 1000;

    public function export(int $campaignSeasonId, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $campaign = CampaignsSeason::findOrFail($campaignSeasonId);
        $quizAttempts = $this->getQuizAttempts($campaignSeasonId);

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Quiz Score
        $quizSheet = $spreadsheet->getActiveSheet();
        $quizSheet->setTitle('Quiz Score');
        $this->writeHeaders($quizSheet, $this->quizHeaders());
        $this->buildQuizSheetChunked($quizSheet, $campaignSeasonId, $startDate, $endDate, $quizAttempts);

        // Sheet 2: Video Score
        $videoSheet = $spreadsheet->createSheet();
        $videoSheet->setTitle('Video Score');
        $this->writeHeaders($videoSheet, $this->videoHeaders());
        $this->buildVideoSheetChunked($videoSheet, $campaignSeasonId, $startDate, $endDate);

        // Sheet 3: Global Score
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Global Score');
        $this->writeHeaders($summarySheet, $this->summaryHeaders());
        $this->buildSummarySheetChunked($summarySheet, $campaignSeasonId);

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'performance_export_' . str_replace(' ', '_', $campaign->title) . '_' . date('Y-m-d_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getQuizAttempts(int $campaignSeasonId): Collection
    {
        return QuizAttempt::where('campaign_season_id', $campaignSeasonId)
            ->get()
            ->keyBy(fn($attempt) => $attempt->user_id . '_' . $attempt->quiz_id);
    }

    private function quizHeaders(): array
    {
        return [
            'Employee Name',
            'Department',
            'Job Title',
            'Campaign',
            'Session',
            'Quiz Title',
            'Question',
            'User Answer',
            'Correct?',
            'Score',
            'Total Quiz Score',
            'Score Gained',
            'Quiz %',
            'Date',
        ];
    }

    private function videoHeaders(): array
    {
        return [
            'Employee Name',
            'Department',
            'Job Title',
            'Campaign',
            'Session',
            'Video URL',
            'User Comment',
            'Expected Keywords',
            'Matched Concepts',
            'Score %',
            'Points Earned',
            'Total Points',
            'Date',
        ];
    }

    private function summaryHeaders(): array
    {
        return [
            'Employee Name',
            'Department',
            'Job Title',
            'Quiz Score',
            'Video Score',
            'Global Score %',
        ];
    }

    private function buildQuizSheetChunked(Worksheet $sheet, int $campaignSeasonId, ?string $startDate, ?string $endDate, Collection $quizAttempts): void
    {
        $query = QuizResponse::with([
            'user.department',
            'quiz.session',
            'quiz.campaignSeason',
            'question',
        ])->whereHas('quiz', fn($q) => $q->where('campaign_season_id', $campaignSeasonId));

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        // Collect all responses grouped by user+quiz to identify last row per group
        $allResponses = collect();
        $query->orderBy('user_id', 'asc')
            ->orderBy('quiz_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->chunk(self::CHUNK_SIZE, function ($responses) use (&$allResponses) {
                $allResponses = $allResponses->concat($responses);
            });

        $total = $allResponses->count();
        $row = 2;

        foreach ($allResponses as $index => $response) {
            $user = $response->user;
            $quiz = $response->quiz;
            $question = $response->question;
            $currentKey = ($user?->id ?? 0) . '_' . ($quiz?->id ?? 0);

            // Check if this is the last row for this user+quiz group
            $nextResponse = ($index + 1 < $total) ? $allResponses[$index + 1] : null;
            $nextKey = $nextResponse
                ? (($nextResponse->user?->id ?? 0) . '_' . ($nextResponse->quiz?->id ?? 0))
                : null;
            $isLastInGroup = ($nextKey !== $currentKey);

            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user ? ($user->first_name . ' ' . $user->last_name) : 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->department?->name ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->job_title ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $quiz?->campaignSeason?->title ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $quiz?->session?->title ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $quiz?->title ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $question?->question_text ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $response->user_answer ?? 'N/A');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $response->is_correct ? 'Yes' : 'No');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $response->is_correct ? ($question?->points ?? 0) : 0);

            if ($isLastInGroup) {
                $attempt = $quizAttempts->get($currentKey);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt?->total_points ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt?->points ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $attempt ? round($attempt->percentage, 2) . '%' : 'N/A');
            } else {
                $col += 3;
            }

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $response->created_at?->format('Y-m-d H:i'));
            $row++;
        }
    }

    private function buildVideoSheetChunked(Worksheet $sheet, int $campaignSeasonId, ?string $startDate, ?string $endDate): void
    {
        $query = ImageSubmissionStep::with([
            'user.department',
            'goSessionStep.goSession',
            'goSessionStep.imageSubmissionGuideline',
        ])
            ->whereHas('goSessionStep.goSession', fn($q) => $q->where('campaign_season_id', $campaignSeasonId))
            ->whereHas('goSessionStep.imageSubmissionGuideline', fn($q) => $q->where('mode', 'video'));

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        $row = 2;
        $query->orderBy('created_at', 'asc')->chunk(self::CHUNK_SIZE, function ($submissions) use ($sheet, &$row) {
            foreach ($submissions as $submission) {
                $user = $submission->user;
                $step = $submission->goSessionStep;
                $guideline = $step?->imageSubmissionGuideline;
                $session = $step?->goSession;

                $expectedKeywords = is_array($guideline?->keywords) ? implode(', ', $guideline->keywords) : 'N/A';
                $matchedConcepts = is_array($submission->matched_concepts) ? implode(', ', $submission->matched_concepts) : 'N/A';

                $col = 1;
                $percentage = $submission->percentage ?? 0;
                $percentage = is_numeric($percentage) ? round($percentage, 2) . '%' : 'N/A';

                $pointsEarned = $submission->points ?? 0;
                $totalPoints = $submission->total_points ?? 0;
                $pointsEarned = is_numeric($pointsEarned) ? $pointsEarned : 'N/A';
                $totalPoints = is_numeric($totalPoints) ? $totalPoints : 'N/A';
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user ? ($user->first_name . ' ' . $user->last_name) : 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->department?->name ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->job_title ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $session?->campaignSeason?->title ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $session?->title ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $guideline?->video_url ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $submission->comment ?? 'N/A');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $expectedKeywords);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $matchedConcepts);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $percentage);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $pointsEarned);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $totalPoints);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $submission->created_at?->format('Y-m-d H:i'));
                $row++;
            }
        });
    }

    private function buildSummarySheetChunked(Worksheet $sheet, int $campaignSeasonId): void
    {
        $row = 2;
        CampaignUserPerformance::with(['user.department'])
            ->where('campaign_season_id', $campaignSeasonId)
            ->orderBy('global_score_percentage', 'desc')
            ->chunk(self::CHUNK_SIZE, function ($performances) use ($sheet, &$row) {
                foreach ($performances as $performance) {
                    $user = $performance->user;

                    $col = 1;
                    $globalPercentage = $performance->global_score_percentage ?? 0;
                    $globalPercentage = is_numeric($globalPercentage) ? round($globalPercentage, 2) . '%' : 'N/A';
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user ? ($user->first_name . ' ' . $user->last_name) : 'N/A');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->department?->name ?? 'N/A');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $user?->job_title ?? 'N/A');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $performance->quiz_score_percentage ?? 0);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $performance->video_score_percentage ?? 0);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $globalPercentage);
                    $row++;
                }
            });
    }

    private function writeHeaders(Worksheet $sheet, array $headers): void
    {
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue($columnLetter . '1', $header);
            $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }
    }
}
