<?php

namespace App\Services;

use App\Models\ChallengeStep;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\AppCommonFunction;
use App\Models\ChallengePoint;
use App\Models\CompanyDepartment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ChallengeService
{
    use AppCommonFunction;

    public function __construct(private ChallengeStep $challenge) {}

    public function find($challenge_id)
    {
        return $this->challenge->find($challenge_id);
    }

    public function getChallenges(Builder $query, $company_id = null, $type = 'all')
    {
        if ($company_id && $type !=='citizen') {
            $query->where('company_id', $company_id);
        }
        if ($type=='citizen') {
            $query->where('company_id', null);
        }
        return $this->getPaginatedData($query);
    }
    public function getChallengesCount($companyId = null)
    {
        $query = $this->challenge->where('status', 'approved');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->count();
    }
    public function getPendingChallengesCount($company_id = null)
    {
        $query = $this->challenge
            ->when($company_id, fn($q) => $q->where('company_id', $company_id))
            ->where('status', 'pending')
            ->whereNull('go_session_step_id');

        $challenges = $query->get();

        return $challenges->count();
    }

    public function create($data)
    {
        return $this->challenge::create($data);
    }
    public function storeEventRelatedData($challenge_step , $data)
    {
        return $challenge_step->event()->create($data);
    }

    public function handleChallenegeImage($image)
    {
        try {
            $filename = uploadFile(
                file: $image,
                disk: 'public',
                path: ChallengeStep::IMAGE_PATH
            );
            return ChallengeStep::IMAGE_PATH . '/' . $filename;
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function update($id, array $data)
    {
        return $this->challenge->find($id)->update($data);
    }
    public function updateEventRelatedData($id , $data)
    {
        $challenge_step = $this->challenge->find($id);
        return !$challenge_step->event?->exists ? $challenge_step->event()->create($data) : $challenge_step->event->update($data);
    }

    public function deleteEventRelatedData($id)
    {
        $challenge_step = $this->challenge->find($id);
        return $challenge_step->event ? $challenge_step->event->delete() : null;
    }

    public function checkIfChallengeHasPoints($challenge_id)
    {
        return ChallengePoint::where('challenge_step_id', $challenge_id)->exists();
    }

    public function delete($challenge)
    {
        return $challenge->delete();
    }

    public function getPendingChallenges($company_id = null, $search = null)
    {
        $query = $this->challenge
            ->when($company_id, fn($q) => $q->where('company_id', $company_id))
            ->where('status', 'pending')
            ->whereNull('go_session_step_id')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        return $this->getPaginatedData($query);
    }

    public function getChallengeAttemptedUsers($challenge_step_id, $search = null)
    {
        $challenges = ChallengePoint::with(['user.company', 'user.department', 'challengeStep'])
            ->where('challenge_step_id', $challenge_step_id)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('company', function ($deptQuery) use ($search) {
                            $deptQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('department', function ($deptQuery) use ($search) {
                            $deptQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate(10);

        return $challenges;
    }

    public function getPendingChallengesDasboard($company_id = null){
        $query = $this->challenge
            ->when($company_id, fn($q) => $q->where('company_id', $company_id))
            ->where('status', 'pending')
            ->whereNull('go_session_step_id')
            ->limit(5);

        return $query->get();
    }

    public function getDepartmentsByCompany($company_id)
    {
        return CompanyDepartment::where('company_id', $company_id)->get();
    }

    public function export($start_date, $end_date, $company_id = null, string $file_name, string $fileType = 'xlsx')
    {
        $user_challenges = $this->challenge
            ->when($company_id, fn($q) => $q->where('company_id', $company_id))
            ->where('status', 'pending')
            ->whereNull('go_session_step_id');

        $user_challenges->when($start_date && $end_date, function ($q) use ($start_date, $end_date) {
            $q->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        });

        $challenges = $user_challenges->orderBy('created_at', 'desc')->get();

        if ($challenges->isEmpty()) {
            $error = match (true) {
                (bool) $company_id && (bool) $start_date && (bool) $end_date => 'No challenges found for this company within the selected date range.',
                (bool) $company_id => 'No challenges found for this company.',
                (bool) $start_date && (bool) $end_date => 'No challenges found for the selected date range.',
                default => 'No challenges found.',
            };

            return redirect()->back()->with('error', $error);
        }

        if (strtolower($fileType) === 'csv') {
            return $this->exportCsv($challenges, $file_name);
        }

        return $this->exportXlsx($challenges, $file_name);
    }

    private function exportCsv($challenges, string $file_name)
    {
        return response()->streamDownload(function () use ($challenges) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'User Name', 'User Email', 'Company', 'Department',
                'Title', 'Description', 'Status', 'Category', 'Image', 'Video URL', 'Created At'
            ]);

            // Rows
            foreach ($challenges as $challenge) {
                fputcsv($handle, [
                    $challenge->createdBy->full_name ?? 'N/A',
                    $challenge->createdBy->email ?? 'N/A',
                    $challenge->company->name ?? 'N/A',
                    $challenge->department->name ?? 'N/A',
                    $challenge->title,
                    $challenge->description,
                    ucwords(str_replace('_', ' ', $challenge->status)),
                    $challenge->category->name ?? 'N/A',
                    $challenge->image_path,
                    $challenge->video_url,
                    $challenge->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $file_name, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportXlsx($challenges, string $file_name)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'User Name',
            'B1' => 'User Email',
            'C1' => 'Company',
            'D1' => 'Department',
            'E1' => 'Title',
            'F1' => 'Description',
            'G1' => 'Status',
            'H1' => 'Category',
            'I1' => 'Image',
            'J1' => 'Video URL',
            'K1' => 'Created At',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;
        foreach ($challenges as $challenge) {
            $sheet->setCellValue("A{$row}", $challenge->createdBy->full_name ?? 'N/A');
            $sheet->setCellValue("B{$row}", $challenge->createdBy->email ?? 'N/A');
            $sheet->setCellValue("C{$row}", $challenge->company->name ?? 'N/A');
            $sheet->setCellValue("D{$row}", $challenge->department->name ?? 'N/A');
            $sheet->setCellValue("E{$row}", $challenge->title);
            $sheet->setCellValue("F{$row}", $challenge->description);
            $sheet->setCellValue("G{$row}", ucwords(str_replace('_', ' ', $challenge->status)));
            $sheet->setCellValue("H{$row}", $challenge->category->name ?? 'N/A');
            $sheet->setCellValue("I{$row}", $challenge->image_path);
            $sheet->setCellValue("J{$row}", $challenge->video_url);
            $sheet->setCellValue("K{$row}", $challenge->created_at->format('Y-m-d H:i:s'));
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
