<?php

namespace App\Services;

use App\Models\GoSession;
use App\Models\GoSessionStep;
use App\Models\SpinWheel;
use App\Models\SpinWheelSubmissionStep;
use App\Traits\AppCommonFunction;
use App\Models\CampaignsSeason;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class SpinWheelValidationStepService
{
    use AppCommonFunction;
    public function __construct(private SpinWheel $spin_wheel, private GoSession $go_session, private GoSessionStep $go_session_step, private SpinWheelSubmissionStep $spin_wheel_submission_step) {}
    public function getSpinWheelStepDetails(array $request): array
    {
        $spinWheelStepDetails = SpinWheel::query()->where('go_session_step_id', $request['go_session_step_id'])->latest()->first();

        if (!$spinWheelStepDetails) {
            return ['success' => false, 'message' => trans('general.spin_wheel_step_details_not_found'), 'data' => []];
        }

        return ['success' => true, 'message' => trans('general.spin_wheel_step_details_fetched'), 'data' => $spinWheelStepDetails];
    }

    public function getSpinWheelList(array $request = [], $company_id = null)
    {
        $per_page = $request['per_page'] ?? 10;
        $query = $this->spin_wheel::query();
        if ($company_id) {
            $query->where('company_id', $company_id);
        }else{
            $query->whereHas('goSessionStep.goSession.campaignSeason', function ($q) {
                $companyCheck = activeCampaignSeasonFilter() === 'campaign' ? 'whereNotNull' : 'whereNull';
                $q->{$companyCheck}('company_id');
            });
        }

        $query = $query->with(['goSessionStep.goSession.campaignSeason.company'])
            ->withCount('attempts')
            ->orderBy('id', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->orderBy('updated_at', 'DESC');

        return $this->getPaginatedData($query, $per_page);
    }

    public function addSpinWheelStep($request = [])
    {
        $go_session_step = $this->go_session_step->where('go_session_id', $request['session'])->where('position', 5)->first();
        if (!$go_session_step) {
            return [
                'status' => false,
                'message' => trans('general.spin_wheel_step_not_found')
            ];
        }
        $spin_wheel_step = $this->spin_wheel->where('go_session_step_id', $go_session_step->id)->first();
        if ($spin_wheel_step) {
            return [
                'status' => false,
                'message' => trans('general.spin_wheel_step_exists'),
            ];
        }
        $spin_wheel = $this->spin_wheel::create([
            'go_session_step_id' => $go_session_step->id,
            'video_url' => $request['video_url'],
            'bonus_leaves' => $request['bonus_leaves'],
            'promo_codes' => $request['promo_codes'],
            'points' => $request['points'],
            'company_id' => $request['company'] ?? null
        ]);
        return [
            'status' => true,
            'message' => trans('general.spin_wheel_step_created'),
            'data' => $spin_wheel
        ];
    }

    public function getSpinWheelDetails($id)
    {
        return $this->spin_wheel::with(['goSessionStep.goSession.campaignSeason.company'])->find($id);
    }

    public function isSpinWheelAttempted($spin_wheel_id)
    {
        return $this->spin_wheel_submission_step->where('spin_wheel_id', $spin_wheel_id)->exists();
    }

    public function updateSpinWheel($id, $request = [])
    {
        $spin_wheel = $this->spin_wheel->where('id', $id)->first();
        if (!$spin_wheel) {
            return [
                'status' => false,
                'message' => trans('general.spin_wheel_step_data_not_found')
            ];
        }
        $go_session_step = $this->go_session_step->where('go_session_id', $request['session'])->where('position', 5)->first();
        if (!$go_session_step) {
            return [
                'status' => false,
                'message' => trans('general.spin_wheel_step_not_found')
            ];
        }
        $is_exist_spin_wheel = $this->spin_wheel->where('go_session_step_id', $go_session_step->id)->exists();
        if ($spin_wheel->go_session_step_id !== $go_session_step->id && $is_exist_spin_wheel) {
            return [
                'status' => false,
                'message' => trans('general.spin_wheel_step_exists')
            ];
        }
        $spin_wheel->go_session_step_id = $go_session_step->id;
        if (isset($request['video_url']))
            $spin_wheel->video_url = $request['video_url'];
        if (isset($request['bonus_leaves']))
            $spin_wheel->bonus_leaves = $request['bonus_leaves'];
        if (isset($request['promo_codes']))
            $spin_wheel->promo_codes = $request['promo_codes'];
        if (isset($request['points']))
            $spin_wheel->points = $request['points'];
        $spin_wheel->save();
        return [
            'status' => true,
            'message' => trans('general.spin_wheel_step_updated'),
            'data' => $spin_wheel
        ];
    }

    public function deleteSpinWheelStep($id)
    {

        $spin_wheel_submission_steps = $this->spin_wheel_submission_step->where('spin_wheel_id', $id)->exists();
        if ($spin_wheel_submission_steps) {
            return ajaxResponse(status: false, message: trans('general.spin_wheel_step_cannot_delete'));
        }
        $this->spin_wheel::destroy($id);
        return ajaxResponse(status: true, message: trans('general.spin_wheel_delete_success'));
    }

    public function getAttemptedUsers($id, $search = null)
    {
        return $this->getStepAttemptedUsers('spinwheel_attempts', 'go_session_step_id', $id, $search);
    }

    public function getCompanyCampaigns($company_id)
    {
        return CampaignsSeason::select('id', 'title')
            ->where('company_id', $company_id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();
    }

    public function export($start_date, $end_date, $reward_type = null, string $file_name, $id, string $fileType = 'xlsx')
    {
        $relation = 'spinwheel_attempts';
        $column   = 'go_session_step_id';

        $usersQuery = \App\Models\User::with([
            'company',
            'department',
            $relation => function ($q) use ($column, $id, $start_date, $end_date, $reward_type) {
                $q->where($column, $id);

                if ($start_date && $end_date) {
                    $q->whereBetween('created_at', [
                        \Carbon\Carbon::parse($start_date)->startOfDay(),
                        \Carbon\Carbon::parse($end_date)->endOfDay(),
                    ]);
                }

                if ($reward_type) {
                    $q->where('bonus_type', $reward_type);
                }
            }
        ]);

        $usersQuery->whereHas($relation, function ($q) use ($column, $id, $start_date, $end_date, $reward_type) {
            $q->where($column, $id);

            if ($start_date && $end_date) {
                $q->whereBetween('created_at', [
                    \Carbon\Carbon::parse($start_date)->startOfDay(),
                    \Carbon\Carbon::parse($end_date)->endOfDay(),
                ]);
            }

            if ($reward_type) {
                $q->where('bonus_type', $reward_type);
            }
        });

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No users found for selected filter.');
        }

        if (strtolower($fileType) === 'csv') {
            return $this->exportCsv($users, $file_name);
        }

        return $this->exportXlsx($users, $file_name);
    }

    private function exportCsv($users, string $file_name)
    {
        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'User ID', 'User Name', 'User Email', 'Company', 'Department',
                'Campaign', 'Points', 'Reward Type', 'Reward', 'Created At'
            ]);

            // Rows
            foreach ($users as $user) {
                foreach ($user->spinwheel_attempts as $attempt) {
                    fputcsv($handle, [
                        $user->id,
                        $user->full_name,
                        $user->email,
                        $user->company->name ?? 'N/A',
                        $user->department->name ?? 'N/A',
                        $attempt->spinwheel?->goSessionStep->goSession->campaignSeason?->title ?? 'N/A',
                        $attempt->points,
                        ucwords(str_replace('_', ' ', $attempt->bonus_type)),
                        $attempt->bonus_value,
                        "'" . $attempt->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            fclose($handle);
        }, $file_name, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportXlsx($users, string $file_name)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'User ID',
            'B1' => 'User Name',
            'C1' => 'User Email',
            'D1' => 'Company',
            'E1' => 'Department',
            'F1' => 'Campaign',
            'G1' => 'Points',
            'H1' => 'Reward Type',
            'I1' => 'Reward',
            'J1' => 'Created At',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;
        foreach ($users as $user) {
            foreach ($user->spinwheel_attempts as $attempt) {
                $sheet->setCellValue("A{$row}", $user->id);
                $sheet->setCellValue("B{$row}", $user->full_name ?? 'N/A');
                $sheet->setCellValue("C{$row}", $user->email ?? 'N/A');
                $sheet->setCellValue("D{$row}", $user->company->name ?? 'N/A');
                $sheet->setCellValue("E{$row}", $user->department->name ?? 'N/A');
                $sheet->setCellValue("F{$row}", $attempt->spinwheel?->goSessionStep->goSession->campaignSeason?->title ?? 'N/A');
                $sheet->setCellValue("G{$row}", $attempt->points);
                $sheet->setCellValue("H{$row}", ucwords(str_replace('_', ' ', $attempt->bonus_type)));
                $sheet->setCellValue("I{$row}", $attempt->bonus_value);
                $sheet->setCellValue("J{$row}", $attempt->created_at->format('Y-m-d H:i:s'));
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
