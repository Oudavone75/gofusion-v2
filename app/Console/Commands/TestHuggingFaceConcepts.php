<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestHuggingFaceConcepts extends Command
{
    protected $signature = 'hf:test-concepts {--comment= : The comment text to test} {--keywords= : The keywords to test, comma separated}';

    protected $description = 'Test the Hugging Face pipeline: language detection → translation → zero-shot classification';

    public function handle()
    {
        $comment = $this->option('comment');
        $keywordsOption = $this->option('keywords');

        if (!$comment) {
            $comment = $this->ask('Enter the comment to test', 'Please ensure proper sorting practices are followed according to national labeling guidelines.');
        }

        if (!$keywordsOption) {
            $keywordsOption = $this->ask('Enter keywords separated by comma', 'tri, recyclage, triman, emballages, filières de collecte');
        }

        $keywords = array_map('trim', explode(',', $keywordsOption));
        $threshold = (float) config('services.huggingface.threshold', 0.45);

        $this->info("========================================");
        $this->line("<comment>Comment:</comment> \"{$comment}\"");
        $this->line("<comment>Keywords:</comment> " . implode(", ", $keywords));
        $this->line("<comment>Threshold:</comment> {$threshold}");

        // ── STEP 1: Detect comment language ──────────────────────────────
        $this->info("\n[Step 1] Detecting comment language...");
        $commentLang = $this->detectLanguage($comment);
        $this->line("<comment>Comment language:</comment> {$commentLang}");

        // ── STEP 2: Detect keyword language ──────────────────────────────
        $keywordLang = 'unknown';
        if ($commentLang !== 'unknown') {
            $this->info("\n[Step 2] Detecting keyword language...");
            $keywordLang = $this->detectLanguage(implode(' ', $keywords));
            $this->line("<comment>Keyword language:</comment> {$keywordLang}");
        } else {
            $this->warn("\n[Step 2] Skipped — comment language unknown.");
        }

        // ── STEP 3: Translate keywords if needed ─────────────────────────
        $reverseMap = [];
        $labelsForClassification = $keywords;

        $supportedPairs = [['en', 'fr'], ['fr', 'en']];
        $needsTranslation = $keywordLang !== 'unknown'
            && $keywordLang !== $commentLang
            && in_array([$keywordLang, $commentLang], $supportedPairs, true);

        if ($needsTranslation) {
            $this->info("\n[Step 3] Translating keywords from [{$keywordLang}] → [{$commentLang}]...");
            $reverseMap = $this->translateKeywords($keywords, $keywordLang, $commentLang);
            $labelsForClassification = array_keys($reverseMap);

            $this->line("<comment>Translation map:</comment>");
            foreach ($reverseMap as $translated => $original) {
                $this->line("  \"{$original}\" → \"{$translated}\"");
            }
        } else {
            $this->warn("\n[Step 3] Translation skipped" . ($commentLang === $keywordLang ? " (same language)" : " (unsupported pair or unknown language)") . ".");
        }

        // ── STEP 4: Zero-shot classification ─────────────────────────────
        $this->info("\n[Step 4] Calling zero-shot classification API...");
        $this->line("<comment>Labels sent to API:</comment> " . implode(", ", $labelsForClassification));

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
            $this->error('HF Zero-Shot API Error: ' . $response->body());
            return Command::FAILURE;
        }

        $result = $response->json();

        // ── STEP 5: Map results back to original labels ───────────────────
        $this->info("\n==== RESULTS ====");
        $this->line("<comment>Raw API response:</comment> " . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $matchedConcepts = [];

        // xlm-roberta-large-xnli returns {labels: [...], scores: [...]}
        $labels = $result['labels'] ?? [];
        $scores = $result['scores'] ?? [];

        foreach ($labels as $index => $translatedLabel) {
            $score = $scores[$index] ?? 0;
            $originalLabel = (!empty($reverseMap) && isset($reverseMap[$translatedLabel]))
                ? $reverseMap[$translatedLabel]
                : $translatedLabel;

            $isMatched = $score >= $threshold;
            $percentage = round($score * 100, 2);

            $displayLabel = $needsTranslation
                ? "\"{$originalLabel}\" (translated: \"{$translatedLabel}\")"
                : "\"{$originalLabel}\"";

            if ($isMatched) {
                $matchedConcepts[] = $originalLabel;
                $this->line("<info>[MATCHED] </info> {$displayLabel} — {$percentage}%");
            } else {
                $this->line("<fg=red>[REJECTED]</> {$displayLabel} — {$percentage}%");
            }
        }

        $numMatched = count($matchedConcepts);
        $total = count($keywords);

        $this->info("\nSummary: {$numMatched} out of {$total} concepts MATCHED.");
        $this->line("Matched Keywords: " . json_encode($matchedConcepts, JSON_UNESCAPED_UNICODE));
        $this->info("========================================\n");

        return Command::SUCCESS;
    }

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
                $this->error('Lang Detection API Error: ' . $response->body());
                return 'unknown';
            }

            $result = $response->json();

            // Unwrap nested array if needed: [[{...}]] → [{...}]
            if (isset($result[0]) && is_array($result[0]) && isset($result[0][0])) {
                $result = $result[0];
            }

            return strtolower($result[0]['label'] ?? 'unknown');
        } catch (\Exception $e) {
            $this->error('Lang Detection Exception: ' . $e->getMessage());
            return 'unknown';
        }
    }

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
                    $this->warn("Translation failed for \"{$keyword}\": " . $response->body());
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
                $this->warn("Translation exception for \"{$keyword}\": " . $e->getMessage());
                $reverseMap[$keyword] = $keyword;
            }
        }

        return $reverseMap;
    }
}
