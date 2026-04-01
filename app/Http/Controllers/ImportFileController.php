<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileImportRequest;
use App\Http\Requests\InspirationalChallengeImportRequest;
use App\Models\CampaignsSeason;
use App\Services\ImportFileDataStoreService;
use App\Services\ImportFileDataValidationService;
use App\Traits\ApiResponse;
use App\Traits\AppCommonFunction;
use App\Traits\ExcelImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ImportFileController extends Controller
{
    use ExcelImport, AppCommonFunction, ApiResponse;

    public function __construct(
        private ImportFileDataStoreService $import_file_data_store_service,
        private ImportFileDataValidationService $import_file_data_validation_service,
    ) {}
    // const SHEETS = ['Quiz','ChallengeToComplete','Event','ChallengeToCreate','SpinWheel','Feedback'];
    const SHEETS = ['Quiz', 'ChallengeToComplete', 'SpinWheel', 'Feedback'];
    public function index()
    {
        $is_admin = request()->routeIs('admin.*');

        if ($is_admin) {
            $companies = $this->getCompanies();
        } else {
            $company = Auth::user()->company;
        }

        return $is_admin
            ? view('admin.import-file.index', compact('companies'))
            : view('company_admin.import-file.index', compact('company'));
    }

    public function import(FileImportRequest $request)
    {
        try {
            $companyId = $request->input('company');

            $campaignId = $request->input('campaign');
            $sheets = [];
            $spreadsheet = $this->load(file: $request->file('file'));

            $selectedSessionIds = $request->input('session');
            $orderedSession = $this->getOrderedSessions(campaignId: $campaignId, selectedSessionIds: $selectedSessionIds);

            foreach (self::SHEETS as $sheetName) {
                $sheetData = $this->getSheetsByName($spreadsheet, $sheetName);
                if (!$sheetData) {
                    return $this->error(message: "Sheet '$sheetName' not found in this file!", code: 500);
                }
                $sheets[$sheetName] = $sheetData->toArray();
            }

            $selectedSessionNumber = collect($orderedSession)->pluck('index')->toArray();

            $missingSessionIdOnQuiz = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['Quiz']));
            $missingSessionIdOnChallenge = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['ChallengeToComplete']));
            $missingSessionIdOnSpinWheel = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['SpinWheel']));
            $missingSessionIdOnFeedback = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['Feedback']));
            if (isset($sheets['Event']) && $sheets['ChallengeToCreate']) {
                $missingSessionIdOnEvent = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['Event']));
                $missingSessionIdOnChallengeToCreate = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['ChallengeToCreate']));
            }

            // $missingSessionIdOnEvent = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['Event']));
            // $missingSessionIdOnChallengeToCreate = array_diff($selectedSessionNumber, $this->fileSessionIds(sheetData: $sheets['ChallengeToCreate']));

            if (!empty($missingSessionIdOnQuiz)) {
                $missingNumber = implode(',', $missingSessionIdOnQuiz);
                return $this->error(message: "Quiz sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }

            if (!empty($missingSessionIdOnChallenge)) {
                $missingNumber = implode(',', $missingSessionIdOnChallenge);
                return $this->error(message: "ChallengeToComplete sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }

            if (!empty($missingSessionIdOnSpinWheel)) {
                $missingNumber = implode(',', $missingSessionIdOnSpinWheel);
                return $this->error(message: "SpinWheel sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }

            if (!empty($missingSessionIdOnFeedback)) {
                $missingNumber = implode(',', $missingSessionIdOnFeedback);
                return $this->error(message: "Feedback sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }

            if (!empty($missingSessionIdOnEvent)) {
                $missingNumber = implode(',', $missingSessionIdOnEvent);
                return $this->error(message: "Event sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }

            if (!empty($missingSessionIdOnChallengeToCreate)) {
                $missingNumber = implode(',', $missingSessionIdOnChallengeToCreate);
                return $this->error(message: "ChallengeToCreate sheet - Missing session data for selected session numbers $missingNumber", code: 500);
            }




            $quizData = [];
            $challengeData = [];
            $spinWheelData = [];
            $feedbackData = [];
            $eventData = [];
            $challengeToCreateData = [];

            $quizError = $this->import_file_data_validation_service->validateQuizSheet(
                sheet: $sheets['Quiz'],
                selectedSessionNumber: $selectedSessionNumber
            );

            if (!empty($quizError)) {
                return $this->error(message: "Invalid Data On Quiz Sheet", result: $quizError, code: 500);
            } else {
                $quizData = $this->extractQuizData(
                    sheet: $sheets['Quiz'],
                    orderedSession: $orderedSession,
                    companyId: $companyId,
                    campaignId: $campaignId
                );
            }

            $challengeError = $this->import_file_data_validation_service->validateChallengeSheet(
                sheet: $sheets['ChallengeToComplete'],
                selectedSessionNumber: $selectedSessionNumber
            );

            if (!empty($challengeError)) {
                return $this->error(message: "Invalid Data On ChallengeToComplete Sheet", result: $challengeError, code: 500);
            } else {
                $challengeData = $this->extractChallengeData(
                    sheet: $sheets['ChallengeToComplete'],
                    orderedSession: $orderedSession,
                    companyId: $companyId,
                    campaignId: $campaignId
                );
            }

            $spinWheelError = $this->import_file_data_validation_service->spinWheelValidate(
                sheet: $sheets['SpinWheel'],
                selectedSessionNumber: $selectedSessionNumber
            );

            if (!empty($spinWheelError)) {
                return $this->error(message: "Invalid Data On SpinWheel Sheet", result: $spinWheelError, code: 500);
            } else {
                $spinWheelData = $this->extractSpinWheelData(
                    sheet: $sheets['SpinWheel'],
                    orderedSession: $orderedSession,
                    companyId: $companyId,
                    campaignId: $campaignId
                );
            }
            $feedbackError = $this->import_file_data_validation_service->validateFeedbackSheet(
                sheet: $sheets['Feedback'],
                selectedSessionNumber: $selectedSessionNumber
            );

            if (!empty($feedbackError)) {
                return $this->error(message: "Invalid Data On Feedback Sheet", result: $feedbackError, code: 500);
            } else {
                $feedbackData = $this->extractFeedbackData(
                    sheet: $sheets['Feedback'],
                    orderedSession: $orderedSession,
                    companyId: $companyId,
                    campaignId: $campaignId
                );
            }

            if (isset($sheets['Event']) && $sheets['Event']) {
                $eventError = $this->import_file_data_validation_service->eventValidate(
                    sheet: $sheets['Event'],
                    selectedSessionNumber: $selectedSessionNumber
                );

                if (!empty($eventError)) {
                    return $this->error(message: "Invalid Data On Event Sheet", result: $eventError, code: 500);
                } else {
                    $eventData = $this->extractEventData(
                        sheet: $sheets['Event'],
                        orderedSession: $orderedSession,
                        companyId: $companyId,
                        campaignId: $campaignId
                    );
                }
            }

            if (isset($sheets['ChallengeToCreate']) && $sheets['ChallengeToCreate']) {
                $challengeToCreateError = $this->import_file_data_validation_service->challengeToCreateValidate(
                    sheet: $sheets['ChallengeToCreate'],
                    selectedSessionNumber: $selectedSessionNumber
                );

                if (!empty($challengeToCreateError)) {
                    return $this->error(message: "Invalid Data On ChallengeToCreate Sheet", result: $challengeToCreateError, code: 500);
                } else {
                    $challengeToCreateData = $this->extractChallengeToCreateData(
                        sheet: $sheets['ChallengeToCreate'],
                        orderedSession: $orderedSession,
                        companyId: $companyId,
                        campaignId: $campaignId
                    );
                }
            }


            if (
                !empty($quizData) &&
                !empty($challengeData) &&
                !empty($spinWheelData) &&
                !empty($feedbackData)
                // !empty($feedbackData) &&
                // !empty($eventData) &&
                // !empty($challengeToCreateData)
            ) {
                $quizResult = $this->import_file_data_store_service->handleQuizStoreData(quizData: $quizData);
                $challengeResult = $this->import_file_data_store_service->handleChallengeStoreData(challengeData: $challengeData);
                $spinWheelResult = $this->import_file_data_store_service->handleSpinWheelStoreData(spinWheelData: $spinWheelData);
                $feedbackResult = $this->import_file_data_store_service->handleFeedbackStoreData(feedbackData: $feedbackData);
                $eventResult = $this->import_file_data_store_service->handleEventStoreData(eventData: $eventData);
                $challengeToCreateResult = $this->import_file_data_store_service->handleChallengeToCreateStoreData(challengeToCreateData: $challengeToCreateData);
            }

            return $this->success(message: 'File imported successfully!', result: [
                'quiz_result' => $quizResult,
                'challenge_result' => $challengeResult,
                'spinWheel_result' => $spinWheelResult,
                'feedback_result' => $feedbackResult,
                'event_result'  => $eventResult,
                'challenge_to_create_result'  => $challengeToCreateResult,
            ]);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }
    public function importInspirationalChallenge(InspirationalChallengeImportRequest $request)
    {
        try {
            $themeId = $request->input('theme_id');
            $companyId = $request->input('company_id');
            $departments = $request->input('departments');

            $spreadsheet = $this->load(file: $request->file('file'));
            $sheet = $spreadsheet->getActiveSheet()->toArray();

            $sheetError = $this->import_file_data_validation_service->validateInspirationalSheet(sheet: $sheet);

            if (!empty($sheetError)) {

                return $this->error(message: "Invalid Data", result: $sheetError, code: 500);
            } else {
                $sheetData = $this->extractInspirationalChallengeData(
                    sheet: $sheet,
                    themeId: $themeId,
                    companyId: $companyId,
                    departments: $departments
                );
            }

            if (!empty($sheetData)) {
                $this->import_file_data_store_service->storeInspirationalChallengeData(inspirationalChallengeData: $sheetData);
                return $this->success(message: 'File imported successfully!', result: [
                    'inspirational_result' => count($sheetData),
                ]);
            }
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    public function getOrderedSessions($campaignId, $selectedSessionIds): array
    {
        $campaign = CampaignsSeason::with(['goSessions' => fn($q) => $q->orderBy('id', 'asc')])->find($campaignId);

        $campaignSessions = $campaign->goSessions->pluck('id');
        $selectedSessions = collect($selectedSessionIds);

        return  $campaignSessions
            ->filter(fn($id) => $selectedSessions->contains($id))
            ->map(function ($id, $index) {
                return [
                    'index' => $index + 1,
                    'id' => $id,
                ];
            })
            ->values()
            ->all();
    }

    public function fileSessionIds($sheetData)
    {
        $sessionIds = [];
        foreach (array_slice($sheetData, 1) as $row) {
            $sessionIds[] = $row[0];
        }
        return $sessionIds;
    }

    public function extractQuizData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $quizData = [];

        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        $questionBlockCount = 10;

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber'] ?? 0] ?? null;
            $title = $row[$headerMap['Title'] ?? 1] ?? null;
            $points = $row[$headerMap['Points'] ?? 2] ?? null;

            if (empty($sessionNumber)) continue;

            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    $questions = [];
                    // Find and process each question block
                    $currentIndex = 0;
                    for ($i = 1; $i <= $questionBlockCount; $i++) {
                        // Find starting index of this question block
                        $questionKey = "Question{$i}";
                        $blockIndex = array_search($questionKey, $header, $currentIndex);
                        if ($blockIndex === false) break;

                        $questionText = $row[$blockIndex] ?? null;
                        $options = array_slice($row, $blockIndex + 1, 5);
                        $correct = $row[$blockIndex + 6] ?? null;

                        if (!empty(trim($questionText ?? ''))) {
                            $question = [
                                "Question" => $questionText,
                                "Options" => array_values(array_map(function ($option) use ($correct) {
                                    return [
                                        "option_text" => $option,
                                        "is_correct"  => Str::lower(trim($option)) === Str::lower(trim($correct))
                                    ];
                                }, array_filter($options, fn($opt) => trim($opt) !== ''))),
                                "Correct" => $correct,
                                "Explanation" => $row[$blockIndex + 7] ?? null
                            ];
                            $questions[] = $question;
                        }

                        $currentIndex = $blockIndex + 8;
                    }

                    $quizData[] = [
                        "companyId" => $companyId,
                        "campaignId" => $campaignId,
                        "sessionId" => $session['id'],
                        "SessionNumber" => $sessionNumber,
                        "Title" => $title,
                        "Points" => $points,
                        "Questions" => $questions
                    ];
                }
            }
        }

        return $quizData;
    }

    public function extractFeedbackData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $quizData = [];

        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        $questionBlockCount = 10;

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber'] ?? 0] ?? null;
            $points = $row[$headerMap['Points'] ?? 2] ?? null;

            if (empty($sessionNumber)) continue;

            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    $questions = [];
                    // Find and process each question block
                    $currentIndex = 0;
                    for ($i = 1; $i <= $questionBlockCount; $i++) {
                        // Find starting index of this question block
                        $questionKey = "Question{$i}";
                        $blockIndex = array_search($questionKey, $header, $currentIndex);
                        if ($blockIndex === false) break;

                        $questionText = $row[$blockIndex] ?? null;
                        $options = array_slice($row, $blockIndex + 1, 5);

                        if (!empty(trim($questionText ?? ''))) {
                            $question = [
                                "Question" => $questionText,
                                "Options" => array_values(array_map(function ($option) {
                                    return [
                                        "option_text" => $option
                                    ];
                                }, array_filter($options, fn($opt) => trim($opt) !== '')))
                            ];
                            $questions[] = $question;
                        }

                        $currentIndex = $blockIndex + 7;
                    }

                    $quizData[] = [
                        "companyId" => $companyId,
                        "campaignId" => $campaignId,
                        "sessionId" => $session['id'],
                        "SessionNumber" => $sessionNumber,
                        "Points" => $points,
                        "Questions" => $questions
                    ];
                }
            }
        }

        return $quizData;
    }

    public function extractChallengeData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $challengeData = [];

        $header = $sheet[0] ?? [];
        $header = array_filter($header, fn($value) => !is_null($value) && $value !== '');
        $header = array_values($header);
        $headerMap = array_flip($header);

        if (
            !isset($headerMap['SessionNumber']) ||
            !isset($headerMap['Title']) ||
            !isset($headerMap['Points']) ||
            !isset($headerMap['Mode'])||
            !isset($headerMap['SampleImageURL']) ||
            !isset($headerMap['AIDescription']) ||
            !isset($headerMap['Description']) ||
            !isset($headerMap['VideoURL']) ||
            !isset($headerMap['Keywords'])
        ) {
            // Cannot extract if headers are missing
            return [];
        }

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;
            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    $points = $row[$headerMap['Points']] ?? null;
                    $title = $row[$headerMap['Title']] ?? null;
                    $description = $row[$headerMap['Description']] ?? '';
                    $mode = $row[$headerMap['Mode']] ?? null;

                    $sampleImage = null;
                    $aiDescription = null;
                    $videoURL = null;
                    $keywords = null;

                    // Set values based on mode
                    if ($mode === 'photo') {
                        $sampleImage = $row[$headerMap['SampleImageURL']] ?? null;
                        $aiDescription = $row[$headerMap['AIDescription']] ?? null;
                    } elseif ($mode === 'video') {
                        $videoURL = $row[$headerMap['VideoURL']] ?? null;
                        $rawKeywords = $row[$headerMap['Keywords']] ?? null;
                        if (!empty(trim($rawKeywords ?? ''))) {
                            $keywords = array_map('trim', explode(',', $rawKeywords));
                            $keywords = array_values(array_filter($keywords, fn($k) => $k !== ''));
                        }
                    }

                    if (!empty($sessionNumber)) {
                        $challengeData[] = [
                            "companyId" => $companyId,
                            "campaignId" => $campaignId,
                            "sessionId" => $session['id'],
                            'SessionNumber' => is_numeric($sessionNumber) ? (int)$sessionNumber : $sessionNumber,
                            'Title' => $title,
                            'Points' => is_numeric($points) ? (int)$points : $points,
                            'Mode' => $mode,
                            'SampleImage' => $sampleImage,
                            'AIDescription' => $aiDescription,
                            'Description' => $description,
                            'VideoURL' => $videoURL,
                            'Keywords' => $keywords
                        ];
                    }
                }
            }
        }

        return $challengeData;
    }

    public function extractSpinWheelData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $spinWheelData = [];

        // Step 1: Get header and build header map
        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        // Step 2: Process each row after header
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;
            $points = $row[$headerMap['Points']] ?? null;
            $videoURL = $row[$headerMap['VideoURL']] ?? null;
            $bonusLeaves = $row[$headerMap['BonusLeaves']] ?? null;
            $promoCode = $row[$headerMap['Surprise']] ?? null;

            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    if (!empty($sessionNumber)) {
                        $spinWheelData[] = [
                            "companyId" => $companyId,
                            "campaignId" => $campaignId,
                            "sessionId" => $session['id'],
                            'SessionNumber' => is_numeric($sessionNumber) ? (int)$sessionNumber : $sessionNumber,
                            'Points' => is_numeric($points) ? (int)$points : $points,
                            'VideoURL' => $videoURL,
                            'BonusLeaves' => is_numeric($bonusLeaves) ? (int)$bonusLeaves : $bonusLeaves,
                            'PromoCode' => is_numeric($promoCode) ? (int)$promoCode : $promoCode,
                        ];
                    }
                }
            }
        }

        return $spinWheelData;
    }

    public function extractEventData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $eventData = [];

        $header = $sheet[0] ?? [];
        $header = array_filter($header, fn($value) => !is_null($value) && $value !== '');
        $header = array_values($header);
        $headerMap = array_flip($header);

        if (
            !isset($headerMap['SessionNumber']) ||
            !isset($headerMap['EventName']) ||
            !isset($headerMap['EventType']) ||
            !isset($headerMap['EventLocation']) ||
            !isset($headerMap['EventStartDate']) ||
            !isset($headerMap['EventEndDate']) ||
            !isset($headerMap['Points']) ||
            !isset($headerMap['SampleImageURL']) ||
            !isset($headerMap['AIDescription']) ||
            !isset($headerMap['Description'])
        ) {
            // Cannot extract if headers are missing
            return [];
        }

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;
            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    $points = $row[$headerMap['Points']] ?? null;
                    $eventName = $row[$headerMap['EventName']] ?? null;
                    $eventType = $row[$headerMap['EventType']] ?? null;
                    $eventLocation = $row[$headerMap['EventLocation']] ?? null;
                    $eventStartDate = $row[$headerMap['EventStartDate']] ?? null;
                    $eventEndDate = $row[$headerMap['EventEndDate']] ?? null;
                    $sampleImage = $row[$headerMap['SampleImageURL']] ?? null;
                    $aiDescription = $row[$headerMap['AIDescription']] ?? null;
                    $description = $row[$headerMap['Description']] ?? null;

                    $eventStartDate = !is_null($eventStartDate) ? trim($eventStartDate) : null;
                    $eventEndDate = !is_null($eventEndDate) ? trim($eventEndDate) : null;

                    $eventStartDate = parseFlexibleDate($eventStartDate);
                    $eventEndDate   = parseFlexibleDate($eventEndDate);

                    if (!empty($sessionNumber)) {
                        $eventData[] = [
                            "companyId" => $companyId,
                            "campaignId" => $campaignId,
                            "sessionId" => $session['id'],
                            'SessionNumber' => is_numeric($sessionNumber) ? (int)$sessionNumber : $sessionNumber,
                            'EventName' => $eventName,
                            'EventType' => $eventType,
                            'EventLocation' => $eventLocation,
                            'EventStartDate' => $eventStartDate,
                            'EventEndDate' => $eventEndDate,
                            'Points' => is_numeric($points) ? (int)$points : $points,
                            'SampleImage' => $sampleImage,
                            'AIDescription' => $aiDescription,
                            'Description' => $description
                        ];
                    }
                }
            }
        }

        return $eventData;
    }

    public function extractChallengeToCreateData(array $sheet, $orderedSession, $companyId, $campaignId): array
    {
        $challengeToCreateData = [];

        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        if (
            !isset($headerMap['SessionNumber']) ||
            !isset($headerMap['Points'])
        ) {
            // Cannot extract if headers are missing
            return [];
        }

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;
            foreach ($orderedSession as $session) {
                if ($session['index'] == $sessionNumber) {
                    $points = $row[$headerMap['Points']] ?? null;

                    if (!empty($sessionNumber)) {
                        $challengeToCreateData[] = [
                            "companyId" => $companyId,
                            "campaignId" => $campaignId,
                            "sessionId" => $session['id'],
                            'SessionNumber' => is_numeric($sessionNumber) ? (int)$sessionNumber : $sessionNumber,
                            'Points' => is_numeric($points) ? (int)$points : $points
                        ];
                    }
                }
            }
        }

        return $challengeToCreateData;
    }

    public function extractInspirationalChallengeData(array $sheet, $themeId, $companyId, $departments): array
    {
        $challengeData = [];

        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        if (
            !isset($headerMap['Number']) ||
            !isset($headerMap['Category']) ||
            !isset($headerMap['Mode'])||
            !isset($headerMap['Title']) ||
            !isset($headerMap['EventName']) ||
            !isset($headerMap['EventType']) ||
            !isset($headerMap['EventLocation']) ||
            !isset($headerMap['EventStartDate']) ||
            !isset($headerMap['EventEndDate']) ||
            !isset($headerMap['Points']) ||
            !isset($headerMap['ImageURL']) ||
            !isset($headerMap['VideoURL']) ||
            !isset($headerMap['AIDescription']) ||
            !isset($headerMap['Description'])
        ) {
            // Cannot extract if headers are missing
            return [];
        }

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['Number']] ?? null;
            if ($sessionNumber === null) {
                continue;
            }

            $category = $row[$headerMap['Category']] ?? null;
            $points = $row[$headerMap['Points']] ?? null;
            $title = $row[$headerMap['Title']] ?? null;
            $sampleImage = $row[$headerMap['ImageURL']] ?? null;
            $videoURL = $row[$headerMap['VideoURL']] ?? null;
            $aiDescription = $row[$headerMap['AIDescription']] ?? null;
            $description = $row[$headerMap['Description']] ?? null;
            $mode = $row[$headerMap['Mode']] ?? null;
            $sampleImage = null;
            $aiDescription = null;
            $videoURL = null;
            $eventName = $row[$headerMap['EventName']] ?? null;
            $eventType = $row[$headerMap['EventType']] ?? null;
            $eventLocation = $row[$headerMap['EventLocation']] ?? null;
            $eventStartDate = $row[$headerMap['EventStartDate']] ?? null;
            $eventEndDate = $row[$headerMap['EventEndDate']] ?? null;

            if ($category == "AttendEvent") {
                $eventStartDate = convertExcelDate($eventStartDate);
                $eventEndDate   = convertExcelDate($eventEndDate);
            } else {
                // Set values based on mode
                if ($mode === 'photo') {
                    $sampleImage = $row[$headerMap['ImageURL']] ?? null;
                    $aiDescription = $row[$headerMap['AIDescription']] ?? null;
                } elseif ($mode === 'video') {
                    $videoURL = $row[$headerMap['VideoURL']] ?? null;
                }
            }

            $challengeData[] = [
                "theme_id" => $themeId,
                "company_id" => $companyId,
                "departments" => $departments,
                'Category' => $category,
                'Mode' => $mode,
                'Title' => $title,
                'Points' => is_numeric($points) ? (int)$points : $points,
                'ImageURL' => $sampleImage,
                'AIDescription' => $aiDescription,
                'Description' => $description,
                'EventName' => $eventName,
                'EventType' => $eventType,
                'EventLocation' => $eventLocation,
                'EventStartDate' => $eventStartDate,
                'EventEndDate' => $eventEndDate,
                'VideoURL' => $videoURL,
            ];
        }

        return $challengeData;
    }

}
