<?php

namespace App\Console\Commands;

use App\Helpers\EmailPrivacyDetector;
use App\Mail\SendMailToPreviousVersionUsers;
use App\Models\Language;
use App\Models\Mode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AddPrevoiusVersionUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-prevoius-version-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = public_path('Joueurs.xlsx');
        // $filePath = public_path('testing.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }
        DB::beginTransaction();
        try {
            $this->info("Starting import from file: {$filePath}");
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) < 2) {
                $this->info('No data rows found in the spreadsheet.');
                DB::rollBack();
                return;
            }
            $removedRow = $rows[2];

            // Re-index the removedRow array to 0,1,2,3...
            $companyData = array_values($removedRow);
            unset($rows[2]);

            $rows = array_values($rows);

            // Read header row and normalize to keys
            $header = array_shift($rows);
            $columns = [];
            foreach ($header as $colLetter => $colTitle) {
                $title = trim((string) $colTitle);
                if ($title === '') {
                    continue;
                }
                // normalize header to snake_case keys
                $key = Str::of($title)->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '_')
                    ->trim('_')
                    ->toString();
                $columns[$colLetter] = $key;
            }

            $created = 0;
            $updated = 0;
            $unSuccessfullyData = [];

            $totalRows = count($rows);
            $this->info("Processing {$totalRows} rows...");
            $progressBar = $this->output->createProgressBar($totalRows);
            $progressBar->start();

            foreach ($rows as $row) {
                $data = [];
                foreach ($columns as $colLetter => $key) {
                    $value = isset($row[$colLetter]) ? trim((string) $row[$colLetter]) : null;
                    if ($value === '') {
                        $value = null;
                    }
                    $data[$key] = $value;
                }
                $user_data = [
                    'city' => 'Paris',
                    'session_time_duration_id' => 1,
                    'referral_source' => 'imported from previous version',
                    'is_enable_notifications' => true,
                    'last_name' => null,
                    'first_name' => null,
                ];
                if (!empty($data['birthdate'])) {
                    $user_data['dob'] = Carbon::createFromFormat('M d, Y h:i a', $data['birthdate'])->format('Y-m-d');
                }
                if (!empty($data['firstname'])) {
                    $user_data['first_name'] = $data['firstname'];
                }
                if (!empty($data['name'])) {
                    $user_data['last_name'] = $data['name'];
                }
                if (!empty($data['surname'])) {
                    $user_data['surname'] = $data['surname'];
                }
                if (!empty($data['email'])) {
                    $username = extractUsernameFromEmail($data['email']);
                    $user_data['username'] = $username;
                    $user_data['email'] = $data['email'];
                }
                $appleEmailDetect = EmailPrivacyDetector::isApplePrivateRelay($user_data['email'] ?? '');
                if ($appleEmailDetect) {
                    $unSuccessfullyData[] = $data;
                    $progressBar->advance();
                    continue;
                }
                if (empty($user_data['first_name']) && empty($user_data['last_name'])) {
                    $unSuccessfullyData[] = $data;
                    $progressBar->advance();
                    continue;
                }

                $user = User::whereEmail($user_data['email'])->first();
                $checkUsername = User::whereUsername($user_data['username'])->first();
                if ($checkUsername) {
                    $unSuccessfullyData[] = $data;
                    $progressBar->advance();
                    if ($user) {
                        $user->email_verified_at = now();
                        $user->save();
                    }
                    continue;
                }
                $companyParseInfo = [];
                if (!empty($data['equans_com'])) {
                    $companyParseInfo = parseCompanyString($companyData[5]);
                    $companyParseInfo['mode'] = 2; // Employee
                } elseif (!empty($data['saintemaris_cholet_eu'])) {
                    $companyParseInfo = parseCompanyString($companyData[6]);
                    $companyParseInfo['mode'] = 4; // Employee
                } elseif (!empty($data['ca_paris_fr'])) {
                    $companyParseInfo = parseCompanyString($companyData[7]);
                    // dd($companyParseInfo);
                    $companyParseInfo['mode'] = 2;
                }
                $companyCode = isset($companyParseInfo['code']) ? $companyParseInfo['code'] : null;
                if ($user) {
                    $password = Str::random(12);
                    $userBuild = $this->buildUser($user_data, $password);
                    $user->update($userBuild);
                    $updated++;
                } else {
                    $password = Str::random(12);
                    $userBuild = $this->buildUser($user_data, $password);
                    $user = User::create($userBuild);
                    $user_details = $this->buildUserDetails($user_data, $user);
                    if (!$user->hasRole('User')) {
                        $user->assignRole('User');
                    }
                    $user->userDetails()->updateOrCreate([], $user_details);
                    if (count($companyParseInfo) > 0) {
                        // if ($companyParseInfo['role'] === 'company') {
                        //     $companyParseInfo['mode'] = 2;
                        // }
                        // if ($companyParseInfo['role'] === 'Student') {
                        //     $companyParseInfo['mode'] = 4;
                        // }
                        // $company = Company::with(['departments'])->where('code', $companyParseInfo['code'])->first();
                        // if ($company) {
                        //     $user->company_id = $company->id;
                        //     $user->modes()->sync([
                        //         'mode_id' => $companyParseInfo['mode']
                        //     ]);
                        //     $user->save();
                        // } else {

                        // }
                        $companyCode = $companyParseInfo['code'];
                    }
                    // $this->attachDefaultMode($user);
                    $created++;
                }

                $this->attachDefaultMode($user);
                // if (!empty($data['ca_paris_fr'])) {
                //     dd($companyCode);
                //  }
                if ($created != 0 || $updated != 0) {
                    Mail::to($user->email)->queue(new SendMailToPreviousVersionUsers($password, $companyCode));
                }
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine(2);
            $downloadLink = null;
            if (count($unSuccessfullyData) > 0) {
                $downloadLink = $this->exportUnsuccessfulData($unSuccessfullyData);
                $this->warn("Unsuccessful records: " . count($unSuccessfullyData));
                $this->info("Download failed records: " . $downloadLink);
            }

            DB::commit();
            $this->info("Import completed. Created: {$created}, Updated: {$updated}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            // optionally rethrow for debugging: throw $e;
        }
    }

    private function exportUnsuccessfulData(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Failed Records');

        // Set headers
        if (count($data) > 0) {
            $headers = array_keys($data[0]);
            $columnIndex = 1;
            foreach ($headers as $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($columnLetter . '1', ucfirst(str_replace('_', ' ', $header)));
                $columnIndex++;
            }

            // Style header row
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC']
                ]
            ];
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

            // Add data rows
            $rowIndex = 2;
            foreach ($data as $row) {
                $columnIndex = 1;
                foreach ($row as $value) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->setCellValue($columnLetter . $rowIndex, $value);
                    $columnIndex++;
                }
                $rowIndex++;
            }

            // Auto-size columns
            foreach (range('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Generate filename with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "failed_users_import_{$timestamp}.xlsx";
        $filePath = public_path($filename);

        // Save the file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return download URL
        return url($filename);
    }

    public function buildUser($user_data, $password)
    {
        return [
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'surname' => $user_data['surname'],
            'username' => $user_data['username'],
            'city' => $user_data['city'],
            'dob' => $user_data['dob'],
            'email' => $user_data['email'],
            'work_email' => $user_data['email'],
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make($password)
        ];
    }

    public function buildUserDetails($request, $user)
    {
        $language_id = $request['language_id'] ?? $this->getDefaultLanguageId();
        $referral_code = Str::uuid();
        return [
            "session_time_duration_id" => $request['session_time_duration_id'],
            "referral_source" => $request['referral_source'],
            "is_enable_notifications" => $request['is_enable_notifications'] ?? false,
            "language_id" => $language_id,
            "refered_by" => $request['refered_by'] ?? null,
            "referral_source" => $request['referral_source'] ?? null,
            "rererral_code" => $referral_code,
            "user_id" => $user->id,
        ];
    }

    public function getDefaultLanguageId()
    {
        return Language::where('label', 'fr')->first()?->id ?? 1;
    }

    public function attachDefaultMode($user)
    {
        $mode = Mode::where('name', 'Citizen')->first();
        return $user->modes()->sync([
            'mode_id' => $mode->id
        ]);
    }
}
