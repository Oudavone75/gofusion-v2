# Cross-Language Keyword Matching for Video Performance

## Context

Users submit comments on video challenges in English, French, or a mix of both. Admin-set keywords can also be in either language. The current `joeddav/xlm-roberta-large-xnli` model handles same-language matching well but drops ~10-20% accuracy when comment and keywords are in different languages. This plan adds a 3-step pipeline inside `VideoPerformanceService` to detect languages and translate keywords to match the comment language before classification — all using free HuggingFace models, with graceful degradation if any step fails.

---

## Files to Modify

1. `app/Services/VideoPerformanceService.php` — main logic changes
2. `config/services.php` — add 2 new config keys

---

## Step 1 — Add Config Keys (`config/services.php`)

Add to the existing `huggingface` array:

```php
'lang_detection_url'     => env('HUGGINGFACE_LANG_DETECTION_URL', 'https://router.huggingface.co/hf-inference/models/papluca/xlm-roberta-base-language-detection'),
'translation_url_prefix' => env('HUGGINGFACE_TRANSLATION_URL_PREFIX', 'https://router.huggingface.co/hf-inference/models/Helsinki-NLP/opus-mt'),
```

---

## Step 2 — Add `detectLanguage()` (`VideoPerformanceService`)

```php
private function detectLanguage(string $text): string
```

- If `$text` is empty → return `'unknown'`
- POST to `config('services.huggingface.lang_detection_url')` with `['inputs' => $text]`, timeout 30s, no retry
- Response format: `[['label' => 'fr', 'score' => 0.98], ...]` (sorted by score desc) → return `$result[0]['label']`
- On any failure (bad response, exception) → log error, return `'unknown'`

---

## Step 3 — Add `translateKeywords()` (`VideoPerformanceService`)

```php
private function translateKeywords(array $keywords, string $fromLang, string $toLang): array
```

- Build URL: `config('services.huggingface.translation_url_prefix') . '-' . $fromLang . '-' . $toLang`
  - e.g. `.../Helsinki-NLP/opus-mt-fr-en` or `.../Helsinki-NLP/opus-mt-en-fr`
- Translate **one keyword at a time** (batching loses per-keyword mapping)
- For each keyword: POST `['inputs' => $keyword]`, timeout 30s
  - Success: `$result[0]['translation_text']` → translated keyword
  - Failure: log error, use **original keyword** (per-keyword graceful degradation)
- Return reverse map: `['translated_keyword' => 'original_keyword', ...]`

---

## Step 4 — Rewrite `classifyKeywords()` with 5-Step Pipeline

Method signature unchanged: `private function classifyKeywords(string $comment, array $keywords): array`

```
A. detectLanguage($comment)         → $commentLang ('en'|'fr'|'unknown')

B. if $commentLang !== 'unknown':
       detectLanguage(implode(' ', $keywords)) → $keywordLang
   else:
       $keywordLang = 'unknown'

C. Decide translation:
   - if $keywordLang === 'unknown' OR $keywordLang === $commentLang → skip (use original keywords, empty reverseMap)
   - if pair is supported ({en,fr} or {fr,en}) → translateKeywords($keywords, $keywordLang, $commentLang) → $reverseMap
   - else → skip

   Supported pair check:
   $supportedPairs = [['en','fr'], ['fr','en']];
   $isSupportedPair = in_array([$keywordLang, $commentLang], $supportedPairs, true);

D. Zero-shot classification (existing logic):
   POST xlm-roberta-large-xnli with:
     'inputs' => $comment
     'parameters' => ['candidate_labels' => array_keys($reverseMap) OR $keywords, 'multi_label' => true]
   timeout 120s, retry(2, 5000) — unchanged

E. Map labels back to original keywords:
   foreach matched item:
     if !empty($reverseMap) && isset($reverseMap[$item['label']]):
         $matchedConcepts[] = $reverseMap[$item['label']]   // original keyword
     else:
         $matchedConcepts[] = $item['label']                // already original
```

---

## Pipeline Flow Diagram

```
classifyKeywords($comment, $keywords)
    │
    ├── detectLanguage($comment)
    │       └── POST papluca/xlm-roberta-base-language-detection → 'fr'|'en'|'unknown'
    │
    ├── detectLanguage(implode(' ', $keywords))   [only if commentLang != 'unknown']
    │       └── POST papluca/xlm-roberta-base-language-detection → 'fr'|'en'|'unknown'
    │
    ├── [only if langs differ AND pair is {en,fr} or {fr,en}]
    │   translateKeywords($keywords, $keywordLang, $commentLang)
    │       └── POST Helsinki-NLP/opus-mt-{from}-{to}  (once per keyword)
    │           returns: ['translatedKw' => 'originalKw', ...]
    │
    ├── POST joeddav/xlm-roberta-large-xnli
    │       input: $comment + translated (or original) keywords
    │       returns: [{'label': '...', 'score': 0.x}, ...]
    │
    └── map translated labels → original keywords via reverseMap
        return: ['originalKeyword1', 'originalKeyword2', ...]
```

---

## API Call Count Summary

| Scenario | Calls |
|---|---|
| Same language (best case) | 2 detect + 0 translate + 1 classify = **3** |
| Different language, 4 keywords | 2 detect + 4 translate + 1 classify = **7** |
| Detection fails (fallback) | 1-2 detect (fail) + 0 translate + 1 classify = **2-3** |

---

## Edge Cases Handled

- Empty comment → returns 0 points (already handled in `evaluateUnderstanding`)
- Language detection fails → `'unknown'` → skip translation, proceed normally
- Translation of a keyword fails → use original keyword for that keyword only
- Mixed-language comment → dominant language used (highest score from detection model)
- Keywords and comment in same language → translation step skipped entirely
- Unsupported language pair (e.g. `de`/`es`) → skip translation, proceed normally

---

## No Changes Needed To

- `ImageValidationStepService.php` — call signature unchanged
- Database schema
- API response format
- Existing env vars

---

## Verification

1. Test: comment in FR, keywords in EN → should match correctly
2. Test: comment in EN, keywords in FR → should match correctly
3. Test: comment in EN, keywords in EN → 2 detect calls only (no translation)
4. Test: comment mixed (`"Je aime this video"`), keywords in EN → dominant lang detected, works
5. Test: HF lang detection API down → logs error, falls back to original behavior, no 500 error
6. Run `php artisan optimize:clear` after config change
