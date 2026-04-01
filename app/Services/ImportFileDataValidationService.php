<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportFileDataValidationService
{
    public function validateQuizSheet(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        // Validate header row
        $header = $sheet[0] ?? [];

        // Try to locate correct header index mapping
        $headerMap = array_flip($header);

        // 1. Ensure required base headers exist
        $requiredHeaders = ['SessionNumber', 'Title', 'Points', 'Explanation'];
        $sessionExplanationIndex = array_search('Explanation', $header);
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // 2. Validate question block headers (Question1...Question10)
        $questionBlockCount = 10;
        $questionBlocks = [];
        for ($i = 1; $i <= $questionBlockCount; $i++) {
            $qIndex = array_search("Question{$i}", $header);
            if ($qIndex !== false) {
                $expected = array_slice($header, $qIndex, 8);
                if ($expected !== ["Question{$i}", "Option1", "Option2", "Option3", "Option4", "Option5", "Correct", "Explanation"]) {
                    $errors[] = "Header block for Question{$i} is malformed";
                } else {
                    $questionBlocks[] = $qIndex;
                }
            }
        }

        // 3. Validate each session row
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber'] ?? 0] ?? 'N/A';

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            // Validate basic required fields
            $title = $row[$headerMap['Title'] ?? 1] ?? null;
            $points = $row[$headerMap['Points'] ?? 2] ?? null;
            $explanation = $row[$sessionExplanationIndex] ?? null;

            if (empty(trim($title ?? ''))) {
                $errors[] = "Session Number {$sessionNumber} Title is required";
            }

            if (empty(trim($explanation ?? ''))) {
                $errors[] = "Session Number {$sessionNumber} Explanation is required";
            }

            if (empty($points)) {
                $errors[] = "Session Number {$sessionNumber} Points are required";
            }elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$sessionNumber}";
            }

            // Check how many valid questions this session has
            $validQuestionCount = 0;

            foreach ($questionBlocks as $blockStartIndex) {
                $questionNumber = (int) filter_var($header[$blockStartIndex], FILTER_SANITIZE_NUMBER_INT);

                $question = $row[$blockStartIndex] ?? null;
                $options = array_slice($row, $blockStartIndex + 1, 5);
                $correct = $row[$blockStartIndex + 6] ?? null;
                $qExplanation = $row[$blockStartIndex + 7] ?? null;

                $hasQuestion = !empty(trim($question ?? ''));
                $filledOptions = array_filter(
                    array_map(fn($opt) => Str::lower(trim($opt)), $options),
                    fn($opt) => $opt !== '' // filter out empty strings
                );

                if ($hasQuestion) {
                    $validQuestionCount++;

                    if (count($filledOptions) < 4) {
                        $errors[] = "Session Number {$sessionNumber} Question {$questionNumber} has less than 4 options";
                    }

                    if (count($filledOptions) > 1 && !in_array(Str::lower(trim($correct)), $filledOptions)) {
                        $errors[] = "Session Number {$sessionNumber} Question {$questionNumber} correct value does not match any option";
                    }

                    if (empty(trim($qExplanation ?? ''))) {
                        $errors[] = "Session Number {$sessionNumber} Question {$questionNumber} Explanation is required";
                    }
                } else {
                    // If question is empty but options/correct are filled, flag inconsistency
                    if (!empty(array_filter(array_merge($options, [$correct]), fn($v) => !empty(trim($v ?? ''))))) {
                        $errors[] = "Session Number {$sessionNumber} Question {$questionNumber} is empty but has options or correct value";
                    }
                }
            }

            if ($validQuestionCount < 2) {
                $errors[] = "Session Number {$sessionNumber} must have at least 2 questions";
            }

            if ($validQuestionCount > 10) {
                $errors[] = "Session Number {$sessionNumber} cannot have more than 10 questions";
            }
        }

        return $errors;
    }
    public function validateChallengeSheet(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        $header = $sheet[0] ?? [];
        $header = array_filter($header, fn($value) => !is_null($value) && $value !== '');
        $header = array_values($header);
        // dd($header);
        $headerMap = array_flip($header);

        // Rule 1: Check if required headers exist
        $requiredHeaders = ['SessionNumber', 'Title','Points','Mode','SampleImageURL','AIDescription','Description','VideoURL','Keywords'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // Only continue if headers are correct
        if (
            !isset($headerMap['SessionNumber']) ||
            !isset($headerMap['Title'])||
            !isset($headerMap['Points'])||
            !isset($headerMap['Mode'])||
            !isset($headerMap['SampleImageURL'])||
            !isset($headerMap['AIDescription'])||
            !isset($headerMap['Description']) ||
            !isset($headerMap['VideoURL']) ||
            !isset($headerMap['Keywords'])
        ) {
            return $errors;
        }

        // Rule 2–5: Validate rows
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            $title = $row[$headerMap['Title']] ?? null;
            $points = $row[$headerMap['Points']] ?? null;
            $mode = $row[$headerMap['Mode']] ?? null;
            $description = $row[$headerMap['Description']] ?? null;

            $sampleImage = null;
            $aiDescription = null;
            $videoURL = null;

            // Set values based on mode
            if ($mode === 'photo') {
                $sampleImage = $row[$headerMap['SampleImageURL']] ?? null;
                $aiDescription = $row[$headerMap['AIDescription']] ?? null;
            } elseif ($mode === 'video') {
                $videoURL = $row[$headerMap['VideoURL']] ?? null;
            }

            // Rule 3: SessionNumber must be provided
            if (empty($sessionNumber)) {
                $errors[] = "Row #{$rowIndex} SessionNumber is required";
            }

            //Title field
            if (empty(trim($title))) {
                $errors[] = "Session Number {$sessionNumber} Title is required";
            }

            // Rule 4: Points must be provided
            if (empty($points) && $points !== 0) {
                $errors[] = "Session Number {$sessionNumber} Points are required";
            }elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$sessionNumber}";
            }

            // Rule 5: Mode must be provided
            if (empty($mode)) {
                $errors[] = "Session Number {$sessionNumber} mode is required";
            } else if ($mode !== 'photo' && $mode !== 'video' && $mode !== 'checkbox') {
                $errors[] = "Session Number {$sessionNumber} mode should be photo, video or checkbox";
            }

            // Rule 6: Points must be between 1–300
            if (!empty($points) && (!is_numeric($points) || $points < 1 || $points > 300)) {
                $errors[] = "Session Number {$sessionNumber} Points must be between 1 and 300";
            }

            // Rule 7: SampleImage must be valid and point to an image
            if ($mode === 'photo' && !empty($sampleImage)) {
                if (!filter_var($sampleImage, FILTER_VALIDATE_URL)) {
                    $errors[] = "SampleImage URL must be a valid URL for session {$sessionNumber}";
                }
            }

            // Rule 8: VideoURL must not be empty and valid
            if ($mode === 'video' && empty($videoURL)) {
                $errors[] = "VideoURL is required for session {$sessionNumber}";
            } elseif ($mode === 'video' && !filter_var($videoURL, FILTER_VALIDATE_URL)) {
                $errors[] = "VideoURL must be a valid URL for session {$sessionNumber}";
            }

            // Rule 9: Keywords required for video mode
            if ($mode === 'video') {
                $keywords = $row[$headerMap['Keywords']] ?? null;
                if (empty(trim($keywords ?? ''))) {
                    $errors[] = "Keywords are required for video mode in session {$sessionNumber}";
                }
            }

            //AI Description field
            if ($mode === 'photo' && empty(trim($aiDescription))) {
                $errors[] = "Session Number {$sessionNumber} AI Description is required";
            }

            //Description field
            // if (empty(trim($description))) {
            //     $errors[] = "Session Number {$sessionNumber} Description is required";
            // }
        }

        return $errors;
    }

    public function spinWheelValidate(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        // Step 1: Get header and build header map
        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        // Step 2: Define required headers
        $requiredHeaders = ['SessionNumber', 'Points', 'VideoURL', 'BonusLeaves', 'Surprise'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // Step 3: Validate each row/session
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // skip header

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            $points = $row[$headerMap['Points']] ?? null;
            $videoURL = $row[$headerMap['VideoURL']] ?? null;
            $bonusLeaves = $row[$headerMap['BonusLeaves']] ?? null;
            $promoCode = $row[$headerMap['Surprise']] ?? null;

            $displaySession = $sessionNumber ?: "at row " . ($rowIndex + 1);

            // Rule 6: SessionNumber must not be empty
            if (empty($sessionNumber)) {
                $errors[] = "SessionNumber is required for session {$displaySession}";
            }

            // Rule 7: Points must not be empty, integer, 1-10
            if ($points === null || $points === '') {
                $errors[] = "Points are required for session {$displaySession}";
            } elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$displaySession}";
            }

            // Rule 8: VideoURL must not be empty and valid
            if (empty($videoURL)) {
                $errors[] = "VideoURL is required for session {$displaySession}";
            } elseif (!filter_var($videoURL, FILTER_VALIDATE_URL)) {
                $errors[] = "VideoURL must be a valid URL for session {$displaySession}";
            }

            // Rule 9: BonusLeaves must not be empty and integer
            if ($bonusLeaves === null || $bonusLeaves === '') {
                $errors[] = "BonusLeaves is required for session {$displaySession}";
            } elseif (!ctype_digit(strval($bonusLeaves))) {
                $errors[] = "BonusLeaves must be an integer for session {$displaySession}";
            }

            // Rule 10: PromoCode must not be empty and integer
            if ($promoCode === null || $promoCode === '') {
                $errors[] = "Surprising Gift is required for session {$displaySession}";
            }
        }

        return $errors;
    }

    public function validateFeedbackSheet(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        // Validate header row
        $header = $sheet[0] ?? [];

        // Try to locate correct header index mapping
        $headerMap = array_flip($header);

        // 1. Ensure required base headers exist
        $requiredHeaders = ['SessionNumber', 'Points'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // 2. Validate question block headers (Question1...Question10)
        $questionBlockCount = 10;
        $questionBlocks = [];
        for ($i = 1; $i <= $questionBlockCount; $i++) {
            $qIndex = array_search("Question{$i}", $header);
            if ($qIndex !== false) {
                $expected = array_slice($header, $qIndex, 6);
                if ($expected !== ["Question{$i}", "Option1", "Option2", "Option3", "Option4", "Option5"]) {
                    $errors[] = "Header block for Question{$i} is malformed";
                } else {
                    $questionBlocks[] = $qIndex;
                }
            }
        }

        // 3. Validate each session row
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row

            $sessionNumber = $row[$headerMap['SessionNumber'] ?? 0] ?? 'N/A';

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            // Validate basic required fields
            $points = $row[$headerMap['Points'] ?? 2] ?? null;

            if (empty($points)) {
                $errors[] = "Session Number {$sessionNumber} Points are required";
            }elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$sessionNumber}";
            }

            // Check how many valid questions this session has
            $validQuestionCount = 0;

            foreach ($questionBlocks as $blockStartIndex) {
                $questionNumber = (int) filter_var($header[$blockStartIndex], FILTER_SANITIZE_NUMBER_INT);

                $question = $row[$blockStartIndex] ?? null;
                $options = array_slice($row, $blockStartIndex + 1, 5);

                $hasQuestion = !empty(trim($question ?? ''));
                $filledOptions = array_filter(
                    array_map(fn($opt) => Str::lower(trim($opt)), $options),
                    fn($opt) => $opt !== '' // filter out empty strings
                );

                if ($hasQuestion) {
                    $validQuestionCount++;

                    if (count($filledOptions) < 1) {
                        $errors[] = "Session Number {$sessionNumber} Question {$questionNumber} has less than 1 options";
                    }

                }
            }

            if ($validQuestionCount < 1) {
                $errors[] = "Session Number {$sessionNumber} must have at least 1 questions";
            }

            if ($validQuestionCount > 10) {
                $errors[] = "Session Number {$sessionNumber} cannot have more than 10 questions";
            }
        }
        return $errors;
    }

    public function eventValidate(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        // Step 1: Get header and build header map
        $header = $sheet[0] ?? [];
        $header = array_filter($header, fn($value) => !is_null($value) && $value !== '');
        $header = array_values($header);
        $headerMap = array_flip($header);

        // Step 2: Define required headers
        $requiredHeaders = ['SessionNumber', 'EventName','EventType','EventLocation','EventStartDate','EventEndDate','Points', 'AIDescription', 'SampleImageURL', 'Description'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // Step 3: Validate each row/session
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // skip header

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            $eventName = $row[$headerMap['EventName']] ?? null;
            $eventType = $row[$headerMap['EventType']] ?? null;
            $eventLocation = $row[$headerMap['EventLocation']] ?? null;
            $eventStartDate = $row[$headerMap['EventStartDate']] ?? null;
            $eventEndDate = $row[$headerMap['EventEndDate']] ?? null;
            $points = $row[$headerMap['Points']] ?? null;
            $sampleImage = $row[$headerMap['SampleImageURL']] ?? null;
            $aiDescription = $row[$headerMap['AIDescription']] ?? null;
            $description = $row[$headerMap['Description']] ?? null;

            $displaySession = $sessionNumber ?: "at row " . ($rowIndex + 1);

            // Rule 6: SessionNumber must not be empty
            if (empty($sessionNumber)) {
                $errors[] = "SessionNumber is required for session {$displaySession}";
            }

            if (empty(trim($eventName))) {
                $errors[] = "Session Number {$sessionNumber} EventName is required";
            }

            if (empty(trim($eventType))) {
                $errors[] = "Session Number {$sessionNumber} EventType is required";
            }

            if (!empty(trim($eventType)) && !in_array(trim($eventType),['onsite','online'])) {
                $errors[] = "Session Number {$sessionNumber} EventType is must be onsite or online";
            }

            if (empty(trim($eventLocation))) {
                $errors[] = "Session Number {$sessionNumber} EventLocation is required";
            }

            $eventStartDate = parseFlexibleDate($eventStartDate);
            $eventEndDate   = parseFlexibleDate($eventEndDate);

            if (empty(trim($eventStartDate))) {
                $errors[] = "Session Number {$sessionNumber} EventStartDate is required";
            }

            if (empty(trim($eventStartDate))) {
                $errors[] = "Session Number {$sessionNumber} EventEndDate is required";
            }

            // Validate date format
            if (!empty($eventStartDate) && !strtotime($eventStartDate)) {
                $errors[] = "Session Number {$sessionNumber} EventStartDate must be a valid date";
            }

            if (!empty($eventEndDate) && !strtotime($eventEndDate)) {
                $errors[] = "Session Number {$sessionNumber} EventEndDate must be a valid date";
            }

            // Compare dates only if both are valid
            if (!empty($eventStartDate) && !empty($eventEndDate) && strtotime($eventStartDate) && strtotime($eventEndDate)) {
                if (strtotime($eventStartDate) > strtotime($eventEndDate)) {
                    $errors[] = "Session Number {$sessionNumber} EventStartDate must be less than or equal to EventEndDate";
                }
                if (strtotime($eventEndDate) < strtotime($eventStartDate)) {
                    $errors[] = "Session Number {$sessionNumber} EventEndDate must be greater than or equal to EventStartDate";
                }
            }

            // Rule 7: Points must not be empty, integer, 1-10
            if ($points === null || $points === '') {
                $errors[] = "Points are required for session {$displaySession}";
            } elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$displaySession}";
            }

            if (!empty($sampleImage)) {
                if (!filter_var($sampleImage, FILTER_VALIDATE_URL)) {
                    $errors[] = "SampleImage URL must be a valid URL for session {$sessionNumber}";
                }
            }

            //AI Description field
            if (empty(trim($aiDescription))) {
                $errors[] = "Session Number {$sessionNumber} AI Description is required";
            }

            //Description field
            if (empty(trim($description))) {
                $errors[] = "Session Number {$sessionNumber} Description is required";
            }
        }
        return $errors;
    }
    public function challengeToCreateValidate(array $sheet,$selectedSessionNumber): array
    {
        $errors = [];

        // Step 1: Get header and build header map
        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        // Step 2: Define required headers
        $requiredHeaders = ['SessionNumber','Points'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }

        // Step 3: Validate each row/session
        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // skip header

            $sessionNumber = $row[$headerMap['SessionNumber']] ?? null;

            if (!in_array($sessionNumber, $selectedSessionNumber)) continue;

            $points = $row[$headerMap['Points']] ?? null;

            $displaySession = $sessionNumber ?: "at row " . ($rowIndex + 1);

            // Rule 6: SessionNumber must not be empty
            if (empty($sessionNumber)) {
                $errors[] = "SessionNumber is required for session {$displaySession}";
            }

            // Rule 7: Points must not be empty, integer, 1-300
            if ($points === null || $points === '') {
                $errors[] = "Points are required for session {$displaySession}";
            } elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for session {$displaySession}";
            }
        }

        return $errors;
    }

    public function validateInspirationalSheet(array $sheet)
    {
        $errors = [];
        // Step 1: Get header and build header map
        $header = $sheet[0] ?? [];
        $headerMap = array_flip($header);

        // Step 2: Define required headers
        $requiredHeaders = ['Number','Category','Mode','Title','Points','ImageURL','AIDescription','Description','VideoURL','EventName','EventType','EventLocation','EventStartDate','EventEndDate'];
        foreach ($requiredHeaders as $col) {
            if (!in_array($col, $header)) {
                $errors[] = "Missing required header: {$col}";
            }
        }



        // Only continue if headers are correct
        if (
            !isset($headerMap['Number']) ||
            !isset($headerMap['Category']) ||
            !isset($headerMap['Mode'])||
            !isset($headerMap['Title'])||
            !isset($headerMap['Points'])||
            !isset($headerMap['ImageURL'])||
            !isset($headerMap['VideoURL'])||
            !isset($headerMap['AIDescription'])||
            !isset($headerMap['Description']) ||
            !isset($headerMap['EventName'])||
            !isset($headerMap['EventType'])||
            !isset($headerMap['EventLocation'])||
            !isset($headerMap['EventStartDate'])||
            !isset($headerMap['EventEndDate'])
        ) {
            return $errors;
        }

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // skip header
            $sessionNumber = $row[$headerMap['Number']] ?? null;
            if ($sessionNumber === null) {
                continue;
            }

            $title = $row[$headerMap['Title']] ?? null;
            $category = $row[$headerMap['Category']] ?? null;
            $mode = $row[$headerMap['Mode']] ?? null;
            $points = $row[$headerMap['Points']] ?? null;
            $sampleImage = $row[$headerMap['ImageURL']] ?? null;
            $aiDescription = $row[$headerMap['AIDescription']] ?? null;
            $description = $row[$headerMap['Description']] ?? null;
            $videoURL = $row[$headerMap['VideoURL']] ?? null;

            $eventName = $row[$headerMap['EventName']] ?? null;
            $eventType = $row[$headerMap['EventType']] ?? null;
            $eventLocation = $row[$headerMap['EventLocation']] ?? null;
            $eventStartDate = $row[$headerMap['EventStartDate']] ?? null;
            $eventEndDate = $row[$headerMap['EventEndDate']] ?? null;

            $sampleImage = null;
            $aiDescription = null;
            $videoURL = null;

            //Category field
            if (empty(trim($category))) {
                $errors[] = "Row Number {$sessionNumber} Category is required";
            }

            if (!empty(trim($category)) && !in_array(trim($category), ['ChallengeToComplete','AttendEvent'])) {
                $errors[] = "Row Number {$sessionNumber} Category must be either ChallengeToComplete or AttendEvent";
            }

            if (trim($category) == 'AttendEvent'){

                if (empty(trim($eventName))) {
                    $errors[] = "Row Number {$sessionNumber} EventName is required";
                }

                if (empty(trim($eventType))) {
                    $errors[] = "Row Number {$sessionNumber} EventType is required";
                }

                if (!empty(trim($eventType)) && !in_array(trim($eventType),['onsite','online'])) {
                    $errors[] = "Row Number {$sessionNumber} EventType is must be onsite or online";
                }

                if (empty(trim($eventLocation))) {
                    $errors[] = "Row Number {$sessionNumber} EventLocation is required";
                }
                $eventStartDate = convertExcelDate($eventStartDate);
                $eventEndDate   = convertExcelDate($eventEndDate);

                if (empty($eventStartDate)) {
                    $errors[] = "Row Number {$sessionNumber} EventStartDate is required";
                }

                if (empty($eventEndDate)) {
                    $errors[] = "Row Number {$sessionNumber} EventEndDate is required";
                }

                // Validate date format
                if (empty($eventStartDate)) {
                    $errors[] = "Row Number {$sessionNumber} EventStartDate is required";
                }

                if (empty($eventEndDate)) {
                    $errors[] = "Row Number {$sessionNumber} EventEndDate is required";
                }

                // Compare only if both dates are valid
                if ($eventStartDate && $eventEndDate) {
                    if ($eventStartDate > $eventEndDate) {
                        $errors[] = "Row Number {$sessionNumber} EventStartDate must be less than or equal to EventEndDate";
                    }
                }
            }

            if (trim($category) == 'ChallengeToComplete'){
                // Mode must be provided
                if (empty($mode)) {
                    $errors[] = "Row Number {$sessionNumber} mode is required";
                } else if ($mode !== 'photo' && $mode !== 'video' && $mode !== 'checkbox') {
                    $errors[] = "Row Number {$sessionNumber} mode should be photo, video or checkbox";
                }

                // Set values based on mode
                if ($mode === 'photo') {
                    $sampleImage = $row[$headerMap['ImageURL']] ?? null;
                    $aiDescription = $row[$headerMap['AIDescription']] ?? null;
                } elseif ($mode === 'video') {
                    $videoURL = $row[$headerMap['VideoURL']] ?? null;
                }

                // title field
                if (empty(trim($title))) {
                    $errors[] = "Row Number {$sessionNumber} Title is required";
                }

                // VideoURL must not be empty and valid
                if ($mode === 'video' && empty($videoURL)) {
                    $errors[] = "VideoURL is required for row {$sessionNumber}";
                } elseif ($mode === 'video' && !filter_var($videoURL, FILTER_VALIDATE_URL)) {
                    $errors[] = "VideoURL must be a valid URL for row {$sessionNumber}";
                }

                // SampleImage must be valid and point to an image
                if ($mode === 'photo' && !empty($sampleImage)) {
                    if (!filter_var($sampleImage, FILTER_VALIDATE_URL)) {
                        $errors[] = "ImageURL URL must be a valid URL for row {$sessionNumber}";
                    }
                }

                // AI Description field
                if ($mode === 'photo' && empty(trim($aiDescription))) {
                    $errors[] = "Row Number {$sessionNumber} AI Description is required";
                }
            }

            if (empty($points) && $points !== 0) {
                $errors[] = "Row Number {$sessionNumber} Points are required";
            } elseif (!ctype_digit(strval($points)) || (int)$points < 1 || (int)$points > 300) {
                $errors[] = "Points must be an integer between 1 and 300 for row {$sessionNumber}";
            }

        }

        return $errors;
    }
}
