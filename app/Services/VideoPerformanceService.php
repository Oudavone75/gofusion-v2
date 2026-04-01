<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoPerformanceService
{
    /**
     * Evaluate the user's understanding of the video based on their comment and the expected keywords.
     * Uses AI to check semantic understanding — not exact word matching.
     *
     * @param string|null $comment User's description/summary of the video
     * @param array $expectedKeywords List of key concepts expected by admin
     * @param int $totalPoints Total points available for this video challenge
     * @return array Contains ['earned_points' => int, 'percentage' => float, 'matched_concepts' => array, 'total_points' => int]
     */
    public function evaluateUnderstanding(?string $comment, array $expectedKeywords, int $totalPoints): array
    {
        $comment = trim($comment ?? '');

        if (empty($expectedKeywords)) {
            return [
                'earned_points' => $totalPoints,
                'percentage' => 100.0,
                'matched_concepts' => [],
                'total_points' => $totalPoints
            ];
        }

        if (!$comment) {
            return [
                'earned_points' => 0,
                'percentage' => 0,
                'matched_concepts' => [],
                'total_points' => $totalPoints
            ];
        }

        $matchedConcepts = $this->classifyKeywords($comment, $expectedKeywords);

        $understoodCount = count($matchedConcepts);
        $totalCount = count($expectedKeywords);

        $percentage = ($understoodCount / $totalCount) * 100;
        $earnedPoints = (int) round($totalPoints * ($understoodCount / $totalCount));

        return [
            'earned_points' => $earnedPoints,
            'percentage' => round($percentage, 2),
            'matched_concepts' => $matchedConcepts,
            'total_points' => $totalPoints
        ];
    }

    /**
     * Full cross-language pipeline:
     * 1. Detect comment language
     * 2. Detect keyword language
     * 3. Translate keywords to comment language if they differ (EN↔FR)
     * 4. Zero-shot classify with xlm-roberta-large-xnli
     * 5. Map translated labels back to original keyword labels
     */
    private function classifyKeywords(string $comment, array $keywords): array
    {
        // Step A — detect comment language
        $commentLang = $this->detectLanguage($comment);

        // Step B — detect keyword language (only if comment language is known)
        $keywordLang = 'unknown';
        if ($commentLang !== 'unknown') {
            $keywordLang = $this->detectLanguage(implode(' ', $keywords));
        }

        // Step C — translate keywords to comment language if languages differ
        $reverseMap = [];
        $labelsForClassification = $keywords;

        $supportedPairs = [['en', 'fr'], ['fr', 'en']];
        $needsTranslation = $keywordLang !== 'unknown'
            && $keywordLang !== $commentLang
            && in_array([$keywordLang, $commentLang], $supportedPairs, true);

        if ($needsTranslation) {
            $reverseMap = $this->translateKeywords($keywords, $keywordLang, $commentLang);
            $labelsForClassification = array_keys($reverseMap);
        }

        // Step D — zero-shot classification
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.huggingface.token'),
            'Content-Type' => 'application/json'
        ])->timeout(120)->retry(2, 5000)->post(
            config('services.huggingface.api_url'),
            [
                'inputs' => $comment,
                'parameters' => [
                    'candidate_labels' => $labelsForClassification,
                    'multi_label' => true,
                ],
            ]
        );

        if (!$response->successful()) {
            Log::error('HF Zero-Shot API Error: ' . $response->body());
            return [];
        }

        $result = $response->json();
        $threshold = (float) config('services.huggingface.threshold');
        $matchedConcepts = [];

        // Step E — map results back to original keyword labels
        // xlm-roberta-large-xnli returns {labels: [...], scores: [...]}
        $labels = $result['labels'] ?? [];
        $scores = $result['scores'] ?? [];

        foreach ($labels as $index => $label) {
            $score = $scores[$index] ?? 0;

            if ($score < $threshold) {
                continue;
            }

            if (!empty($reverseMap) && isset($reverseMap[$label])) {
                $matchedConcepts[] = $reverseMap[$label];
            } else {
                $matchedConcepts[] = $label;
            }
        }

        return $matchedConcepts;
    }

    /**
     * Detect the language of a text using papluca/xlm-roberta-base-language-detection.
     * Returns 'en', 'fr', or 'unknown' on failure.
     */
    private function detectLanguage(string $text): string
    {
        $text = trim($text);
        if (empty($text)) {
            return 'unknown';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.huggingface.token'),
                'Content-Type' => 'application/json'
            ])->timeout(30)->post(
                config('services.huggingface.lang_detection_url'),
                ['inputs' => $text]
            );

            if (!$response->successful()) {
                Log::error('HF Lang Detection API Error: ' . $response->body());
                return 'unknown';
            }

            $result = $response->json();

            // Response: [[{'label': 'fr', 'score': 0.98}, ...]] or [{'label': 'fr', 'score': 0.98}, ...]
            // Unwrap nested array if needed
            if (isset($result[0]) && is_array($result[0]) && isset($result[0][0])) {
                $result = $result[0];
            }

            if (!isset($result[0]['label'])) {
                return 'unknown';
            }

            return strtolower($result[0]['label']);
        } catch (\Exception $e) {
            Log::error('HF Lang Detection Exception: ' . $e->getMessage());
            return 'unknown';
        }
    }

    /**
     * Translate keywords from one language to another using Helsinki-NLP/opus-mt models.
     * Translates one keyword at a time to preserve per-keyword mapping.
     * Returns a reverse map: ['translated_keyword' => 'original_keyword']
     */
    private function translateKeywords(array $keywords, string $fromLang, string $toLang): array
    {
        $url = config('services.huggingface.translation_url_prefix') . '-' . $fromLang . '-' . $toLang;
        $reverseMap = [];

        foreach ($keywords as $keyword) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.huggingface.token'),
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post($url, ['inputs' => $keyword]);

                if (!$response->successful()) {
                    Log::error('HF Translation API Error for keyword "' . $keyword . '": ' . $response->body());
                    $reverseMap[$keyword] = $keyword;
                    continue;
                }

                $result = $response->json();
                $translated = $result[0]['translation_text'] ?? null;

                if ($translated) {
                    $reverseMap[$translated] = $keyword;
                } else {
                    $reverseMap[$keyword] = $keyword;
                }
            } catch (\Exception $e) {
                Log::error('HF Translation Exception for keyword "' . $keyword . '": ' . $e->getMessage());
                $reverseMap[$keyword] = $keyword;
            }
        }

        return $reverseMap;
    }
}
