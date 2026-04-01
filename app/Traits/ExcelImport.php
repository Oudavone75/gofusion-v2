<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ExcelImport
{
    public function load($file)
    {
        try {
            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            return $reader->load($file);
        }catch (\Exception $exception){
            throw new HttpException(500, $exception->getMessage());
        }
    }

    public function getQuizSheet($spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        return $sheet->toArray();
    }

    public function getSheetsByName($spreadsheet , $sheetName)
    {
        return $spreadsheet->getSheetByName($sheetName);
    }

    public function getHeading($sheet)
    {
        return $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, true, false);
    }

    public function filterQuestions($rows,$header,$from,$to,$limit)
    {
        return collect($rows)
            ->slice(1)
            ->map(fn($row) => array_combine($header, $row))
            ->filter(function ($item) use ($from, $to) {
                $number = isset($item['number']) ? (int) trim($item['number']) : null;
                return $number !== null && $number >= $from && $number <= $to;
            })
            ->when($limit, fn($collection) => $collection->take($limit))
            ->values()
            ->toArray();
    }

    public function extractQuestionsDateWise($rows)
    {
        $data = [];
        $currentDate = null;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Keep track of the current date (J1, J2, etc.)
            if (!empty($row[0])) {
                $currentDate = $row[0];
            }

            if (!$currentDate) continue;

            if (!isset($data[$currentDate])) {
                $data[$currentDate] = [];
            }

            // Loop through 3 questions (cols 1, 2, 3)
            for ($q = 1; $q <= 3; $q++) {
                if (!isset($data[$currentDate]['question_' . $q])) {
                    $data[$currentDate]['question_' . $q] = [];
                }

                // Add current row value
                $data[$currentDate]['question_' . $q][] = $row[$q];
            }
        }

        // Convert to structured format
        $structured = [];

        foreach ($data as $date => $questions) {
            $structured[$date] = [];

            foreach ($questions as $key => $qArray) {
                // Skip empty or invalid rows
                if (count($qArray) < 3) continue;

                // Remove null/empty values
                $qArray = array_filter($qArray, fn($v) => !is_null($v) && trim($v) !== '');

                // Remove heading/title row (index 0)
                $questionText = trim($qArray[1]); // The actual question
                $options = array_slice($qArray, 2);

                $parsedOptions = [];

                foreach ($options as $opt) {
                    $isCorrect = str_contains($opt, '✅');
                    $cleanText = trim(str_replace('✅', '', $opt));
                    $parsedOptions[] = [
                        'name' => preg_replace('/^[A-D]\.\s*/', '', $cleanText),
                        'isCorrect' => $isCorrect,
                    ];
                }

                $structured[$date][] = [
                    'question' => $questionText,
                    'options' => $parsedOptions,
                ];
            }
        }

        return $structured;
    }
}
