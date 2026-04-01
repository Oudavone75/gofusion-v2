<?php

namespace App\Services;

use App\Models\UserCarbonFootprint;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CampaignsSeason;
use App\Services\UserScoreService;
use App\Http\Resources\UserResource;
use App\Services\UserTransactionService;
use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CarbonFootprintService
{
    public function __construct(private UserScoreService $user_score_service, private UserTransactionService $user_transaction_service) {}

    /**
     * Check if the user has a carbon footprint record for the current month.
     *
     * @param int $userId
     * @return bool
     */
    public function getCurrentMonthCarbonFootprint(int $userId, string $mode = 'bool')
    {
        $currentMonthRecord = UserCarbonFootprint::where('user_id', $userId)
            ->whereMonth('attempt_at', now()->month)
            ->whereYear('attempt_at', now()->year)
            ->orderBy('attempt_at', 'DESC')
            ->first();

        if ($mode == 'record') {

            return $currentMonthRecord;
        }
        return $currentMonthRecord ? true : false;
    }

    public function saveCarbonFootprints(array $data)
    {
        if (isset($data['token'])) {
            $user_id = Crypt::decrypt($data['token']);
        } else {
            $user_id = Auth::id();
        }
        $user_carbon_footprint = UserCarbonFootprint::create([
            'user_id' => $user_id,
            'attempt_at' => now(),
            'carbon_unit' => $data['carbon_unit'],
            'carbon_value' => $data['carbon_value'],
            'water_unit' => $data['water_unit'],
            'water_value' => $data['water_value']
        ]);
        return [
            'success' => true,
            'message' => trans('general.carbon_attempted'),
            'data' => isset($data['token']) ? $user_carbon_footprint : $this->getUserDetails()
        ];
    }

    public function getUserDetails()
    {
        $user = User::with(['userDetails', 'company.mode', 'department', 'modes'])->withCount('userCompleteSessions')->find(Auth::id());
        if ($user->isCitizen()) {
            $current_campaign_season = CampaignsSeason::where('company_id', null)->where('status', 'active')->first();
        } else {
            $current_campaign_season = CampaignsSeason::where('company_id', $user->company_id)->where('status', 'active')->first();
        }
        $request_data = [];
        $user_leaves = 0;
        $level = config('constants.LEVELS.10');
        if ($current_campaign_season) {
            $request_data['campaign_season_id'] = $current_campaign_season->id;
            $last_attempted_step = $this->user_score_service->getUserLastAttemptedStep($request_data, $user);
            $ranking = $this->user_score_service->getUserRanking($request_data, $user);
            $level = $this->user_score_service->getUserLevel(config('constants.LEVELS'), $current_campaign_season, $user);
        }
        $user_leaves = $this->user_score_service->getTotalLeaves($user);
        $last_attempted_step = $last_attempted_step ?? [];
        $ranking = $ranking ?? [
            'campaign_or_season_wise_raking' => [
                'points' => 0,
                'rank' => 0
            ],
            'company_wise_ranking' => [
                'points' => 0,
                'rank' => 0
            ],
            'department_wise_ranking' => [
                'points' => 0,
                'rank' => 0
            ]
        ];
        $response['user'] = new UserResource(
            $user,
            $this,
            $last_attempted_step,
            $level,
            $user_leaves,
            $ranking,
            $this->user_transaction_service
        );
        return $response;
    }

    public function exportAsCSV($query, $fileName)
    {
        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, [
                'First Name',
                'Last Name',
                'Email',
                'Attempted At',
                'Water Value',
                'Water Unit',
                'Carbon Value',
                'Carbon Unit'
            ]);

            $totalWater = 0;
            $totalCarbon = 0;
            $totalCount = 0;

            // Stream data in chunks
            $query->chunk(1000, function ($assessments) use ($file, &$totalWater, &$totalCarbon, &$totalCount) {
                foreach ($assessments as $assessment) {
                    $user = $assessment->user;

                    $nameParts = explode(' ', $user->full_name ?? '', 2);
                    $firstName = $nameParts[0] ?? 'N/A';
                    $lastName = $nameParts[1] ?? '';
                    $waterValue = is_numeric($assessment->water_value) ? number_format($assessment->water_value, 0, '.', '') : floatval($assessment->water_value);
                    $carbonValue = is_numeric($assessment->carbon_value) ? number_format($assessment->carbon_value, 1, '.', '') : floatval($assessment->carbon_value);
                    // dd($waterValue);
                    fputcsv($file, [
                        $firstName,
                        $lastName,
                        $user->email ?? 'N/A',
                        $assessment->attempt_at ? date('d F Y', strtotime($assessment->attempt_at)) : 'N/A',
                        number_format($waterValue, 0, '.', ''),
                        $assessment->water_unit,
                        number_format($carbonValue, 1, '.', ''),
                        $assessment->carbon_unit
                    ]);

                    $totalWater += floatval($assessment->water_value);
                    $totalCarbon += floatval($assessment->carbon_value);
                    $totalCount++;
                }

                // Free memory
                unset($assessments);
            });

            // Add summary rows
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Summary']);
            fputcsv($file, ['Total Assessments', $totalCount]);
            fputcsv($file, ['Total Water Usage', number_format($totalWater, 0) . ' litres']);
            fputcsv($file, ['Total Carbon Footprint', number_format($totalCarbon, 1) . ' tonnes']);

            if ($totalCount > 0) {
                fputcsv($file, ['Average Water per Assessment', number_format($totalWater / $totalCount, 0) . ' L']);
                fputcsv($file, ['Average Carbon per Assessment', number_format($totalCarbon / $totalCount, 1) . ' T']);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Export as Excel (For smaller datasets)
     */
    public function exportAsExcel($query, $fileName)
    {
        // Limit to prevent memory issues
        $count = $query->count();

        if ($count > 100000) {
            return redirect()->back()->with('error', 'Too many records (' . number_format($count) . '). Please use CSV format or apply date filters.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'First Name',
            'B1' => 'Last Name',
            'C1' => 'Email',
            'D1' => 'Attempted At',
            'E1' => 'Water Value',
            'F1' => 'Water Unit',
            'G1' => 'Carbon Value',
            'H1' => 'Carbon Unit',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4A90E2');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;
        $totalWater = 0;
        $totalCarbon = 0;
        $totalCount = 0;

        // Process in chunks
        $query->chunk(1000, function ($assessments) use ($sheet, &$row, &$totalWater, &$totalCarbon, &$totalCount) {
            foreach ($assessments as $assessment) {
                $user = $assessment->user;

                $nameParts = explode(' ', $user->full_name ?? '', 2);
                $firstName = $nameParts[0] ?? 'N/A';
                $lastName = $nameParts[1] ?? '';

                $sheet->setCellValue("A{$row}", $firstName);
                $sheet->setCellValue("B{$row}", $lastName);
                $sheet->setCellValue("C{$row}", $user->email ?? 'N/A');
                $sheet->setCellValue("D{$row}", $assessment->attempt_at ? date('d F Y', strtotime($assessment->attempt_at)) : 'N/A');

                // Set numeric values explicitly
                $sheet->setCellValueExplicit("E{$row}", floatval($assessment->water_value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValue("F{$row}", $assessment->water_unit);
                $sheet->setCellValueExplicit("G{$row}", floatval($assessment->carbon_value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValue("H{$row}", $assessment->carbon_unit);

                // Apply number format
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.0');

                $totalWater += floatval($assessment->water_value);
                $totalCarbon += floatval($assessment->carbon_value);
                $totalCount++;
                $row++;
            }

            unset($assessments);
            gc_collect_cycles();
        });

        // Summary
        $row += 2;

        $summaryFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFBF00']
            ]
        ];

        $sheet->setCellValue("A{$row}", 'Summary');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($summaryFill);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Assessments');
        $sheet->setCellValue("B{$row}", $totalCount);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Water Usage');
        $sheet->setCellValue("B{$row}", number_format($totalWater, 0) . ' litres');
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Carbon Footprint');
        $sheet->setCellValue("B{$row}", number_format($totalCarbon, 1) . ' tonnes');
        $row++;

        if ($totalCount > 0) {
            $sheet->setCellValue("A{$row}", 'Average Water per Assessment');
            $sheet->setCellValue("B{$row}", number_format($totalWater / $totalCount, 0) . ' litres');
            $row++;

            $sheet->setCellValue("A{$row}", 'Average Carbon per Assessment');
            $sheet->setCellValue("B{$row}", number_format($totalCarbon / $totalCount, 1) . ' tonnes');
        }

        // Download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
