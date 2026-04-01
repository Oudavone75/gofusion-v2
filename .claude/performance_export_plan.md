# Performance Excel Export Plan

## Context
The client wants admins to export all quiz and video performance data per employee per campaign to a multi-sheet Excel file (.xlsx). This is a dedicated admin page at `/admin/performance/export` where the admin selects a campaign, optional date range, and downloads the file.

**Bug to fix along the way:** `CampaignUserPerformance.campaignSeason()` references `CampaignSeason::class` which doesn't exist — should be `CampaignsSeason::class`.

---

## Excel Output: 3 Sheets

### Sheet 1 — "Quiz Details"
| Employee Name | Department | Job Title | Campaign | Session | Quiz Title | Question | User Answer | Correct? | Score | Date |

One row per `QuizResponse`. Score = question points if correct, 0 if wrong.

### Sheet 2 — "Video Details"
| Employee Name | Department | Job Title | Campaign | Session | Video URL | User Comment | Expected Keywords | Matched Concepts | Score % | Points Earned | Date |

One row per `ImageSubmissionStep` where guideline mode = 'video'. Keywords/matched_concepts are JSON arrays displayed as comma-separated strings.

### Sheet 3 — "Summary"
| Employee Name | Department | Job Title | Quiz Score % | Video Score % | Global Score % |

One row per user from `CampaignUserPerformance`.

---

## Files to Create

### 1. `app/Services/PerformanceExportService.php`
**Method:** `export(int $campaignSeasonId, ?string $startDate, ?string $endDate): StreamedResponse`

Data queries:
- Quiz: `QuizResponse::with(['user.department', 'quiz.session', 'quiz.campaignSeason', 'question'])->whereHas('quiz', fn($q) => $q->where('campaign_season_id', $id))`
- Video: `ImageSubmissionStep::with(['user.department', 'goSessionStep.goSession', 'goSessionStep.imageSubmissionGuideline'])->whereHas('goSessionStep.goSession', fn($q) => $q->where('campaign_season_id', $id))->whereHas('goSessionStep.imageSubmissionGuideline', fn($q) => $q->where('mode', 'video'))`
- Summary: `CampaignUserPerformance::with(['user.department'])->where('campaign_season_id', $id)`

Private helpers:
- `buildQuizSheet(Worksheet $sheet, Collection $responses): void`
- `buildVideoSheet(Worksheet $sheet, Collection $submissions): void`
- `buildSummarySheet(Worksheet $sheet, Collection $performances): void`
- `applyHeaderStyle(Worksheet $sheet, string $range): void`

Follow pattern from `ImageValidationStepService::exportExcel()` (app/Services/ImageValidationStepService.php:516).

### 2. `app/Http/Controllers/Admin/PerformanceExportController.php`
- `showExportPage()` — loads campaigns list, returns view
- `export(Request $request)` — validates `campaign_season_id` (required), `start_date`/`end_date` (optional), calls service

### 3. `resources/views/admin/performance/export.blade.php`
- Extends `admin.layout.main`
- Campaign season dropdown
- Optional date range picker (datetimepicker)
- "Export" button (POST form)
- Follow pattern from existing export pages (e.g., carbon-assessment)

---

## Files to Modify

### 4. `routes/backoffice/admin.php`
Add use statement + routes inside admin.auth group:
```php
use App\Http\Controllers\Admin\PerformanceExportController;

Route::prefix('performance')->name('performance.')->group(function () {
    Route::get('/export', [PerformanceExportController::class, 'showExportPage'])->name('export.page');
    Route::post('/export', [PerformanceExportController::class, 'export'])->name('export');
});
```

### 5. `app/Models/CampaignUserPerformance.php`
Fix line 18: `CampaignSeason::class` → `CampaignsSeason::class`

### 6. Admin sidebar navigation (layout view)
Add "Performance Export" link.

---

## Implementation Order
1. Fix `CampaignUserPerformance` model bug
2. Create `PerformanceExportService` with 3-sheet Excel generation
3. Create `PerformanceExportController`
4. Add routes
5. Create export view
6. Add sidebar link

## Verification
1. `php artisan route:list --name=performance` — confirm routes registered
2. Visit `/admin/performance/export` — page loads with campaign dropdown
3. Select a campaign, click Export — verify downloaded .xlsx has 3 sheets
4. Check Quiz Details sheet: correct employee data, questions, answers, scores
5. Check Video Details sheet: video URL, comments, keywords, matched concepts, scores
6. Check Summary sheet: per-employee quiz/video/global percentages
