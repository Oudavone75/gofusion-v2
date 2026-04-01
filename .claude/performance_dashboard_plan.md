# Performance Dashboard Plan

## Context
Add a performance summary section to admin and company admin dashboards, plus a dedicated employee drill-down page. The dashboard shows campaign-level stats (avg scores, charts) and an employee listing with links to detailed per-employee performance pages.

**Both panels (Admin + Company Admin)** get the same feature. Company admin is scoped to `company_id`.

---

## Design Decisions
- Dashboard performance section is **added below** existing dashboard content (stat cards + challenges table stay)
- Default to **most recent active campaign**, with dropdown to switch campaigns
- Employee drill-down is a **separate dedicated page** (`/admin/performance/{userId}?campaign_season_id=X`)
- Employee detail page shows data for the **selected campaign** only
- Charts use **ApexCharts** (already loaded in both layouts)
- Campaign switching uses **AJAX** to reload the performance section without full page refresh

---

## Part 1: Dashboard Performance Summary Section

### What it shows (for selected campaign):

#### Row 1: 4 Score Cards
| Avg Quiz Score % | Avg Video Score % | Avg Global Score % | Total Employees |
Computed from `CampaignUserPerformance` for the selected campaign.

#### Row 2: 2 Charts
- **Left chart** — Bar chart: Score distribution (0-20%, 20-40%, 40-60%, 60-80%, 80-100%) showing employee count per bucket
- **Right chart** — Donut/pie chart: Quiz vs Video average score comparison

#### Row 3: Employee Performance Table (DataTable)
| # | Employee Name | Department | Job Title | Quiz Score % | Video Score % | Global Score % | Action |
- Sortable, searchable via DataTables
- "Action" column has a "View Details" link → employee drill-down page
- Data from `CampaignUserPerformance` for selected campaign

---

## Part 2: Employee Drill-Down Page

**Route:** `/admin/performance/{userId}?campaign_season_id=X`

### Layout:
#### Header: Employee Info Card
| Name | Department | Job Title | Campaign | Global Score % |

#### Section 1: Quiz Performance
- Summary card: Quiz Score %, Earned Points / Total Points
- Table: All quiz attempts for this user in the campaign
  | Quiz Title | Session | Score | Total | Percentage | Date |
  Data from `QuizAttempt` where `user_id` and `campaign_season_id`

#### Section 2: Video Performance
- Summary card: Video Score %, Earned Points / Total Points
- Table: All video submissions for this user in the campaign
  | Video Title | Session | Comment | Keywords Matched | Score % | Points | Date |
  Data from `ImageSubmissionStep` filtered by video mode, user, and campaign

---

## Files to Create

### 1. `app/Services/PerformanceDashboardService.php`
Shared service used by both admin and company admin.

Methods:
- `getDashboardStats(int $campaignSeasonId, ?int $companyId = null): array`
  Returns: avgQuizScore, avgVideoScore, avgGlobalScore, totalEmployees, scoreDistribution, employees collection
- `getEmployeeDetail(int $userId, int $campaignSeasonId): array`
  Returns: user info, performance summary, quiz attempts, video submissions

### 2. Admin Controller: `app/Http/Controllers/Admin/PerformanceDashboardController.php`
- `dashboardStats(Request $request)` — AJAX endpoint returning JSON stats for selected campaign
- `employeeDetail(int $userId, Request $request)` — Renders employee detail page

### 3. Company Admin Controller: `app/Http/Controllers/CompanyAdmin/PerformanceDashboardController.php`
- Same methods but scoped to `auth()->user()->company_id`

### 4. Views:
- `resources/views/admin/partials/performance-dashboard.blade.php` — The performance section partial (included in dashboard)
- `resources/views/admin/performance/employee-detail.blade.php` — Employee drill-down page
- `resources/views/company_admin/partials/performance-dashboard.blade.php` — Same partial for company admin
- `resources/views/company_admin/performance/employee-detail.blade.php` — Same detail page for company admin

---

## Files to Modify

### 5. `app/Http/Controllers/Admin/DashboardController.php`
- Add `$campaigns` (all campaign seasons) to the `index()` method return data
- Add `$activeCampaign` (most recent active campaign)

### 6. `app/Http/Controllers/CompanyAdmin/DashboardController.php`
- Same changes, scoped to company

### 7. `resources/views/admin/dashboard.blade.php`
- Include the performance dashboard partial below existing content

### 8. `resources/views/company_admin/dashboard.blade.php`
- Include the performance dashboard partial below existing content

### 9. `routes/backoffice/admin.php`
Add routes:
```php
use App\Http\Controllers\Admin\PerformanceDashboardController;

Route::prefix('performance')->name('performance.')->group(function () {
    // existing export routes...
    Route::get('/dashboard-stats', [PerformanceDashboardController::class, 'dashboardStats'])->name('dashboard-stats');
    Route::get('/employee/{userId}', [PerformanceDashboardController::class, 'employeeDetail'])->name('employee-detail');
});
```

### 10. `routes/backoffice/company_admin.php`
Same routes for company admin.

### 11. Admin sidebar + Company admin sidebar
Add "Performance Dashboard" link (or rename existing "Performance Export" to a submenu with "Dashboard" + "Export").

---

## Implementation Order
1. Create `PerformanceDashboardService` with `getDashboardStats()` and `getEmployeeDetail()`
2. Create admin `PerformanceDashboardController` + routes
3. Create dashboard partial view with charts + employee table
4. Modify admin `DashboardController` to pass campaigns
5. Include partial in admin dashboard view
6. Create employee detail view + wire up
7. Repeat for company admin (controller, routes, views)
8. Update sidebars

## Verification
1. `php artisan route:list --name=performance` — confirm all routes
2. Visit admin dashboard — performance section shows below existing cards
3. Change campaign dropdown — stats refresh via AJAX
4. Click "View Details" on an employee — opens drill-down page with quiz + video tables
5. Repeat verification on company admin panel
6. Company admin only sees employees from their company
