<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\ImportFileController;
use App\Http\Controllers\Admin\PerformanceDashboardController;
use App\Http\Controllers\Admin\PerformanceExportController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\GoSessionController;
use App\Http\Controllers\Admin\GoSessionStepController;
use App\Http\Controllers\Admin\ImageStepController;
use App\Http\Controllers\Admin\EventStepController;
use App\Http\Controllers\Admin\SpinWheelController;
use App\Http\Controllers\Admin\ChallengeStepController;
use App\Http\Controllers\Admin\SurveyFeedbackController;
use App\Http\Controllers\Admin\ChallengeController;
use App\Http\Controllers\Admin\NewsCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewsFeedController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\CompanyContactController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PostController;

const COMMON_URI = [
    'CREATE'    => 'create',
    'STORE'     => 'store',
    'INDEX'     => 'list',
    'VIEW'      => 'view',
    'EDIT'      => 'edit',
    'UPDATE'    => 'update',
    'DELETE'    => 'delete',
];

const ID_PARAM = '/{id}';
const COMPANY_ID_PARAM = '/{company}';
const DEPARTMENT_ID_PARAM = '/{company_department}';
const CAMPAIGN_ID_PARAM = '/{campaign}';

Route::get('/admin', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::group(['prefix' => 'admin'], function () {
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordView'])->name('forgot.password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot.password.post');
    Route::get('/reset-password', [AuthController::class, 'resetPasswordView'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.post');
});

// Protected admin routes
Route::middleware(['admin.auth'])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('company')->name('company.')->group(function () {

        Route::middleware('check.permission:view companies')->group(function () {
            Route::get(COMMON_URI['INDEX'], [CompanyController::class, 'index'])->name('index');
            Route::get(COMMON_URI['VIEW'] . COMPANY_ID_PARAM, [CompanyController::class, 'show'])->name('show');
            Route::get('/company-users/{company}', [CompanyController::class, 'companyUsers'])->name('company-users')
                ->middleware('check.permission:view companies users');
            });

        Route::delete('/delete-company-user/{user_id}', [UserController::class, 'delete'])->name('company-users.delete');

        Route::middleware('check.permission:create companies')->group(function () {
            Route::get(COMMON_URI['CREATE'], [CompanyController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [CompanyController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit companies')->group(function () {
            Route::get(COMMON_URI['EDIT'] . ID_PARAM, [CompanyController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . COMPANY_ID_PARAM, [CompanyController::class, 'update'])->name('update');
        });

        Route::delete(COMMON_URI['DELETE'] . ID_PARAM, [CompanyController::class, 'delete'])->name('delete');
        Route::get('/has-active-campaigns' . COMPANY_ID_PARAM, [CompanyController::class, 'hasActiveCampaigns']);

        // Join Token Management
        Route::post('/{company}/generate-join-token', [CompanyController::class, 'generateJoinToken'])->name('generate-join-token');
        Route::post('/revoke-join-token/{token}', [CompanyController::class, 'revokeJoinToken'])->name('revoke-join-token');
        Route::get('/{company}/join-tokens', [CompanyController::class, 'getJoinTokens'])->name('join-tokens');
    });

    Route::prefix('department')->name('department.')->group(function () {

        Route::middleware('check.permission:view departments')->group(function () {
            Route::get(COMMON_URI['INDEX'], [DepartmentController::class, 'index'])->name('index');
        });

        Route::middleware('check.permission:create departments')->group(function () {
            Route::get(COMMON_URI['CREATE'], [DepartmentController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [DepartmentController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit departments')->group(function () {
            Route::get(COMMON_URI['EDIT'] . ID_PARAM, [DepartmentController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . DEPARTMENT_ID_PARAM, [DepartmentController::class, 'update'])->name('update');
        });

        Route::middleware('check.permission:delete departments')->group(function () {
            Route::delete(COMMON_URI['DELETE'] . DEPARTMENT_ID_PARAM, [DepartmentController::class, 'delete'])->name('delete');
        });

        Route::get('/has-active-campaigns' . DEPARTMENT_ID_PARAM, [DepartmentController::class, 'hasDepartmentActiveCampaigns'])
            ->name('has-active-campaigns');
    });

    Route::prefix('campaign')->name('campaign.')->group(function () {

        Route::middleware('check.permission:view campaigns')->group(function () {
            Route::get(COMMON_URI['INDEX'], [CampaignController::class, 'index'])->name('index');
            Route::get(COMMON_URI['VIEW'] . CAMPAIGN_ID_PARAM, [CampaignController::class, 'view'])->name('view');
        });

        Route::middleware('check.permission:create campaigns')->group(function () {
            Route::get(COMMON_URI['CREATE'], [CampaignController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [CampaignController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit campaigns')->group(function () {
            Route::get(COMMON_URI['EDIT'] . CAMPAIGN_ID_PARAM, [CampaignController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . CAMPAIGN_ID_PARAM, [CampaignController::class, 'update'])->name('update');
        });

        Route::middleware('check.permission:delete campaigns')->group(function () {
            Route::delete(COMMON_URI['DELETE'] . CAMPAIGN_ID_PARAM, [CampaignController::class, 'delete'])->name('delete');
        });

        Route::middleware('check.permission:manage campaigns status')->group(function () {
            Route::post(CAMPAIGN_ID_PARAM . '/change-status', [CampaignController::class, 'changeStatus'])->name('change-status');
        });

        Route::get('/departments/by-company' . COMPANY_ID_PARAM, [CampaignController::class, 'getByCompany'])->name('departments.by-company');
    });

    Route::prefix('sessions')->name('sessions.')->group(function () {

        Route::get('import-sessions', [GoSessionController::class, 'showImportPage'])
            ->name('import.page')->middleware('check.permission:import sessions');

        Route::post('import-sessions', [GoSessionController::class, 'import'])
            ->name('import');

        Route::controller(GoSessionController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->name('index')
                    ->middleware('check.permission:view sessions');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create sessions');

                Route::post('/', 'store')
                    ->name('store')
                    ->middleware('check.permission:create sessions');

                Route::get('/{session}', 'show')
                    ->name('show')
                    ->middleware('check.permission:view sessions');

                Route::get('/{session}/edit', 'edit')
                    ->name('edit')
                    ->middleware('check.permission:edit sessions');

                Route::put('/{session}', 'update')
                    ->name('update')
                    ->middleware('check.permission:edit sessions');

                Route::delete('/{session}', 'destroy')
                    ->name('destroy')
                    ->middleware('check.permission:delete sessions');
        });
    });

    Route::group(['prefix' => 'steps', 'as' => 'steps.'], function () {
        Route::get(COMMON_URI['INDEX'], [GoSessionStepController::class, 'index'])->name('index');
    });

    Route::prefix('quiz')->name('quiz.')->group(function () {

        Route::middleware('check.permission:view quiz')->group(function () {
            Route::get(COMMON_URI['INDEX'], [QuizController::class, 'index'])->name('index');
            Route::get(COMMON_URI['VIEW'] . '/{quiz}', [QuizController::class, 'view'])->name('view');
            Route::get('attempted-users/{quiz}/{type}', [QuizController::class, 'attemptedUsers'])->name('attempted-users')
                ->middleware('check.permission:view quiz attempted users');
        });

        Route::middleware('check.permission:create quiz')->group(function () {
            Route::get(COMMON_URI['CREATE'], [QuizController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [QuizController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit quiz')->group(function () {
            Route::get(COMMON_URI['EDIT'] . '/{quiz}', [QuizController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . '{quiz}', [QuizController::class, 'update'])->name('update');
        });

        Route::middleware('check.permission:delete quiz')->group(function () {
            Route::delete(COMMON_URI['DELETE'] . '{quiz}', [QuizController::class, 'delete'])->name('delete');
        });

        Route::post('/import', [QuizController::class, 'import'])->name('import');
        Route::get('/export/{id}', [QuizController::class, 'export'])->name('export');
    });

    Route::prefix('spin-wheel')->name('spin.')->group(function () {

        Route::middleware('check.permission:view spinwheel')->group(function () {
            Route::get(COMMON_URI['INDEX'], [SpinWheelController::class, 'list'])->name('index');
            Route::get(COMMON_URI['VIEW'] . ID_PARAM, [SpinWheelController::class, 'view'])->name('view');
            Route::get('spin/attempted-users/{spin_wheel}/{type}', [SpinWheelController::class, 'attemptedUsers'])
                ->name('attempted-users')
                ->middleware('check.permission:view spinwheel attempted users');
        });

        Route::middleware('check.permission:create spinwheel')->group(function () {
            Route::get(COMMON_URI['CREATE'], [SpinWheelController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [SpinWheelController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit spinwheel')->group(function () {
            Route::get(COMMON_URI['EDIT'] . ID_PARAM, [SpinWheelController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . ID_PARAM, [SpinWheelController::class, 'update'])->name('update');
        });

        Route::middleware('check.permission:delete spinwheel')->group(function () {
            Route::delete(COMMON_URI['DELETE'] . ID_PARAM, [SpinWheelController::class, 'delete'])->name('destroy');
        });

        Route::middleware('check.permission:export spinwheel attempted users')->group(function () {
            Route::get('/export/{id}', [SpinWheelController::class, 'export'])->name('export');
        });
    });

    Route::prefix('survey-feedback')->name('survey-feedback.')->group(function () {

        Route::middleware('check.permission:view survey feedback')->group(function () {
            Route::get(COMMON_URI['INDEX'], [SurveyFeedbackController::class, 'index'])->name('index');
            Route::get(COMMON_URI['VIEW'] . '/{survey_feedback}', [SurveyFeedbackController::class, 'view'])->name('view');
            Route::get('attempted-users/{survey_feedback}/{type}', [SurveyFeedbackController::class, 'attemptedUsers'])
                ->name('attempted-users')
                ->middleware('check.permission:view survey feedback attempted users');
        });

        Route::middleware('check.permission:create survey feedback')->group(function () {
            Route::get(COMMON_URI['CREATE'], [SurveyFeedbackController::class, 'create'])->name('create');
            Route::post(COMMON_URI['STORE'], [SurveyFeedbackController::class, 'store'])->name('store');
        });

        Route::middleware('check.permission:edit survey feedback')->group(function () {
            Route::get(COMMON_URI['EDIT'] . ID_PARAM, [SurveyFeedbackController::class, 'edit'])->name('edit');
            Route::put(COMMON_URI['UPDATE'] . '{survey_feedback}', [SurveyFeedbackController::class, 'update'])->name('update');
        });

        Route::middleware('check.permission:delete survey feedback')->group(function () {
            Route::delete(COMMON_URI['DELETE'] . '{survey_feedback}', [SurveyFeedbackController::class, 'delete'])->name('delete');
        });

        Route::middleware('check.permission:export survey feedback attempted users')->group(function () {
            Route::get('/export/{id}', [SurveyFeedbackController::class, 'export'])->name('export');
        });
    });

    Route::prefix('citizens')->name('citizens.')->group(function () {

        Route::middleware('check.permission:view citizens')->group(function () {
            Route::get(COMMON_URI['INDEX'], [UserController::class, 'index'])->name('index');
        });

        Route::middleware('check.permission:manage citizens status')->group(function () {
            Route::post('toggle-status/{user_id}', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });
        Route::delete(COMMON_URI['DELETE'] . ID_PARAM, [UserController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => 'profile/'], function () {
        Route::get('/', [UserController::class, 'profile'])->name('profile.index');
        Route::put(COMMON_URI['UPDATE'], [UserController::class, 'update'])->name('profile.update');
    });

    Route::prefix('change-password')->name('change.')->group(function () {
        Route::get('/', [AuthController::class, 'changePassword'])->name('index');
        Route::post('/', [AuthController::class, 'updatePassword'])->name('update');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('get-company-campaigns/{company_id}', [CampaignController::class, 'getCompanyCampaigns'])->name('get-company-campaigns');
    Route::get('get-seasons', [CampaignController::class, 'getSeasons'])->name('get-seasons');
    Route::get('get-campaign-sessions/{campaign_id}', [GoSessionController::class, 'getCampaignSessions'])->name('get-campaign-sessions');

    Route::prefix('images')->name('images.')->group(function () {

        Route::controller(ImageStepController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->name('index')
                    ->middleware('check.permission:view challenges');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create challenges');

                Route::post('/', 'store')
                    ->name('store')
                    ->middleware('check.permission:create challenges');

                Route::get('/{image_step}', 'show')
                    ->name('show')
                    ->middleware('check.permission:view challenges');

                Route::get('/{image_step}/edit', 'edit')
                    ->name('edit')
                    ->middleware('check.permission:edit challenges');

                Route::put('/{image_step}', 'update')
                    ->name('update')
                    ->middleware('check.permission:edit challenges');

                Route::delete('/{image_step}', 'destroy')
                    ->name('destroy')
                    ->middleware('check.permission:delete challenges');
            });

        Route::get('attempted-users/{image_step}/{type}', [ImageStepController::class, 'attemptedUsers'])
            ->name('attempted-users')
            ->middleware('check.permission:view challenges attempted users');

        Route::get('appealing-users/{image_step}/{type}', [ImageStepController::class, 'appealingUsers'])
            ->name('appealing-users')
            ->middleware('check.permission:view challenges user requests');

        Route::post('appealing-users/change-status/{image_step}', [ImageStepController::class, 'changeAppealingStatus'])
            ->name('appealing-users.change-status')
            ->middleware('check.permission:manage challenges user requests');

        Route::get('export/{go_session_step_id}', [ImageStepController::class, 'export'])
            ->name('export');
    });

    Route::resource('events', EventStepController::class);
    Route::get('events/attempted-users/{event_step}', [EventStepController::class, 'attemptedUsers'])->name('events.attempted-users');
    Route::resource('challenges-step', ChallengeStepController::class);
    Route::get('challenges-step/attempted-users/{challenge_step}', [ChallengeStepController::class, 'attemptedUsers'])->name('challenges-step.attempted-users');
    Route::patch('/challenges-step/{status}/status', [ChallengeStepController::class, 'challengeStatus'])->name('challenges-step.status');
    Route::get('challenges-step/attempted-user-details/{user_id}/{go_session_step_id}', [ChallengeStepController::class, 'attemptedUserDetails'])->name('challenges-step.attempted-user.details');
    Route::get('images-step/attempted-user-details/{user_id}/{go_session_step_id}/{type}', [ImageStepController::class, 'attemptedUserDetails'])->name('images-step.attempted-user.details');

    Route::prefix('inspiration-challenges')->name('inspiration-challenges.')->group(function () {

        Route::get('attempted-users/{challenge_step_id}', [ChallengeController::class, 'getAttemptedUsersList'])
            ->name('attempted-users-list')
            ->middleware('check.permission:view inspiration challenges attempted users');

        Route::patch('{inspiration_challenge}/change-status/{status}', [ChallengeController::class, 'changeStatus'])
            ->name('change-status')
            ->middleware('check.permission:manage inspiration challenges user requests');

        Route::middleware('check.permission:view inspiration challenges user requests')->group(function () {
            Route::get('pending-inspiration-challenges', [ChallengeController::class, 'getUserRequests'])
                ->name('pending');
            Route::get('pending-inspiration-challenges/user-details/{challenge_id}', [ChallengeStepController::class, 'getInspirationChallengeDetails'])
                ->name('pending.details');
            Route::get('pending-inspiration-challenges/export', [ChallengeController::class, 'export'])
                ->name('export');
        });

        Route::middleware('check.permission:manage inspiration challenges user requests')->group(function () {
            Route::patch('pending-inspiration-challenges/{challenge_id}/{status}/status', [ChallengeStepController::class, 'inspirationChallengeStatus'])
                ->name('pending.status');
        });

        Route::get('import-inspiration-challenges', [ChallengeController::class, 'import'])
            ->name('import')
            ->middleware('check.permission:manage inspiration challenges import');

        Route::controller(ChallengeController::class)->group(function () {
            Route::get('/', 'index')
                ->name('index')
                ->middleware('check.permission:view inspiration challenges');
            Route::get('/create', 'create')
                ->name('create')
                ->middleware('check.permission:create inspiration challenges');
            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create inspiration challenges');
            Route::get('/{inspiration_challenge}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit inspiration challenges');
            Route::put('/{inspiration_challenge}', 'update')
                ->name('update')
                ->middleware('check.permission:edit inspiration challenges');
            Route::delete('/{inspiration_challenge}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete inspiration challenges');
            Route::get('/{inspiration_challenge}', 'show')
                ->name('show')
                ->middleware('check.permission:view inspiration challenges');
        });
    });

    Route::prefix('news-category')
        ->name('news-category.')
        ->controller(NewsCategoryController::class)
        ->group(function () {
            Route::get('/', 'index')
                ->name('index')
                ->middleware('check.permission:view news categories');

            Route::get('/create', 'create')
                ->name('create')
                ->middleware('check.permission:create news categories');

            Route::post('/', 'store')
                ->name('store')
                ->middleware('check.permission:create news categories');

            Route::get('/{news_category}', 'show')
                ->name('show')
                ->middleware('check.permission:view news categories');

            Route::get('/{news_category}/edit', 'edit')
                ->name('edit')
                ->middleware('check.permission:edit news categories');

            Route::put('/{news_category}', 'update')
                ->name('update')
                ->middleware('check.permission:edit news categories');

            Route::delete('/{news_category}', 'destroy')
                ->name('destroy')
                ->middleware('check.permission:delete news categories');
    });

    Route::prefix('news-feed')->name('news-feed.')->group(function () {

        Route::controller(NewsFeedController::class)
            ->group(function () {

                Route::get('/', 'index')
                    ->name('index')
                    ->middleware('check.permission:view news feeds');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('check.permission:create news feeds');

                Route::post('/', 'store')
                    ->name('store')
                    ->middleware('check.permission:create news feeds');

                Route::get('/{news_feed}', 'show')
                    ->name('show')
                    ->middleware('check.permission:view news feeds');

                Route::get('/{news_feed}/edit', 'edit')
                    ->name('edit')
                    ->middleware('check.permission:edit news feeds');

                Route::put('/{news_feed}', 'update')
                    ->name('update')
                    ->middleware('check.permission:edit news feeds');

                Route::delete('/{news_feed}', 'destroy')
                    ->name('destroy')
                    ->middleware('check.permission:delete news feeds');
            });

        Route::post('/toggle-status/{news_id}', [NewsFeedController::class, 'toggleStatus'])
            ->name('toggle-status')
            ->middleware('check.permission:manage news feeds status');
    });

    Route::group(['prefix' => 'import-file', 'as' => 'import-file.'], function () {
        Route::get('/', [ImportFileController::class, 'index'])->name('index')->middleware('check.permission:manage imports');
        Route::post('/import', [ImportFileController::class, 'import'])->name('import');
        Route::post('/import-inspirational-challenge', [ImportFileController::class, 'importInspirationalChallenge'])->name('import-inspirational-challenge');
    });

    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/export', [PerformanceExportController::class, 'showExportPage'])->name('export.page');
        Route::post('/export', [PerformanceExportController::class, 'export'])->name('export');
        Route::get('/dashboard-stats', [PerformanceDashboardController::class, 'dashboardStats'])->name('dashboard-stats');
        Route::get('/employee/{userId}', [PerformanceDashboardController::class, 'employeeDetail'])->name('employee-detail');
    });

    Route::prefix('rewards')->name('rewards.')->group(function () {
        Route::get('/', [RewardController::class, 'index'])->name('index')->middleware('check.permission:view rewards');
        Route::get('/campaign/{campaign}/{type?}', [RewardController::class, 'view'])->name('campaign.view');
        Route::post('/store/{campaign}', [RewardController::class, 'store'])->name('store')->middleware('check.permission:give rewards');
        Route::get('/custom-rewards/list', [RewardController::class, 'customRewardsList'])->name('custom.index');
        Route::get('/custom-rewards/create', [RewardController::class, 'customRewardsCreate'])->name('custom.create');
        Route::get('/get-unrewarded-campaigns/{company_id}', [RewardController::class, 'getUnrewardedCampaigns'])->name('custom.get-unrewarded-campaigns');
        Route::get('/get-unrewarded-seasons', [RewardController::class, 'getUnrewardedSeasons'])->name('custom.get-unrewarded-seasons');
        Route::post('/custom-store', [RewardController::class, 'customRewardsStore'])->name('custom.store');
        Route::get('/custom-rewards/edit/{id}', [RewardController::class, 'customRewardsEdit'])->name('custom.edit');
        Route::get('/custom-rewards/view/{id}', [RewardController::class, 'customRewardsView'])->name('custom.view');
        Route::post('/toggle-custom-reward-status/{campaign_season_id}', [RewardController::class, 'toggleCustomRewardStatus'])->name('custom.toggle-status')->middleware('check.permission:give rewards');
    });

    Route::prefix('company-contact')->name('company-contact.')->group(function () {

        Route::resource('/', CompanyContactController::class)->parameters([
            '' => 'company_contact'
        ])->middleware([
            'index' => 'check.permission:view contact requests',
            'show' => 'check.permission:view contact requests',
        ]);

        Route::post('/mark-all-as-read', [CompanyContactController::class, 'markAllAsRead'])
            ->name('mark-all-as-read');
    });

    Route::prefix('gallery')->name('gallery.')->group(function () {

        Route::get('/', [GalleryController::class, 'index'])
            ->name('index')
            ->middleware('check.permission:view gallery');

        Route::middleware('check.permission:create gallery')->group(function () {
            Route::get('/create', [GalleryController::class, 'create'])->name('create');
            Route::post('/store', [GalleryController::class, 'store'])->name('store');
        });

        Route::delete('/delete/{id}', [GalleryController::class, 'delete'])
            ->name('delete')
            ->middleware('check.permission:delete gallery');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('index')
            ->middleware('check.permission:view notifications');

        Route::middleware('check.permission:create notifications')->group(function () {
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/store', [NotificationController::class, 'store'])->name('store');
        });

        Route::get('/view/{id}', [NotificationController::class, 'show'])
            ->name('show')
            ->middleware('check.permission:view notifications');

        Route::get('/recipients/{id}', [NotificationController::class, 'getRecipients'])
            ->name('recipients')
            ->middleware('check.permission:view recipients');
    });

    Route::prefix('sub-admins')->name('sub-admins.')->group(function () {
        Route::get('/list', [AuthController::class, 'getSubAdmins'])->name('list');
        Route::get('/create', [AuthController::class, 'createSubAdmin'])->name('create');
        Route::post('/store', [AuthController::class, 'storeSubAdmin'])->name('store');
        Route::get('/edit/{id}', [AuthController::class, 'editSubAdmin'])->name('edit');
        Route::put('/update/{id}', [AuthController::class, 'updateSubAdmin'])->name('update');
        Route::delete('/delete/{id}', [AuthController::class, 'deleteSubAdmin'])->name('delete');
        Route::get('/view/{id}', [AuthController::class, 'showSubAdmin'])->name('view');
        Route::post('toggle-status/{id}', [AuthController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('carbon-assessment')->name('carbon-assessment.')->group(function () {
        Route::middleware('check.permission:view carbon assessments')->group(function () {
            Route::get('/', [DashboardController::class, 'carbonAssessment'])->name('index');
        });
        Route::middleware('check.permission:export carbon assessments')->group(function () {
            Route::post('/export', [DashboardController::class, 'exportCarbonAssessments'])->name('export');
            Route::get('/export-count', [DashboardController::class, 'getExportCount'])->name('count');
        });
    });

    Route::prefix('social-feed')->name('social-feed.')->group(function () {
        Route::middleware('check.permission:view posts')->group(function () {
            Route::get('/list', [PostController::class, 'index'])->name('list');
            Route::get('/view/{id}', [PostController::class, 'show'])->name('view');
        });
        Route::middleware('check.permission:create posts')->group(function () {
            Route::get('/create', [PostController::class, 'create'])->name('create');
            Route::post('/store', [PostController::class, 'store'])->name('store');
        });
        Route::middleware('check.permission:edit posts')->group(function () {
            Route::get('/edit/{id}', [PostController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [PostController::class, 'update'])->name('update');
        });
        Route::middleware('check.permission:create posts')->group(function () {
            Route::delete('/delete/{id}', [PostController::class, 'destroy'])->name('delete');
        });
        Route::middleware('check.permission:manage posts status')->group(function () {
            Route::post('toggle-status/{id}', [PostController::class, 'toggleStatus'])->name('toggle-status');
        });
        Route::delete('/delete-media/{id}', [PostController::class, 'deleteMedia'])->name('delete-media');
        Route::middleware('check.permission:view posts reports')->group(function () {
            Route::get('/reported-posts/list', [PostController::class, 'getReportedPosts'])->name('reported-posts-list');
        });
        Route::middleware('check.permission:view reported users')->group(function () {
            Route::get('/reported-users/list/{id}', [PostController::class, 'reportedUsersList'])->name('reported-users-list');
            Route::get('/reported-users/detail/{id}', [PostController::class, 'reportedUsersDetail'])->name('reported-users-detail');
        });
        Route::middleware('check.permission:manage posts reports')->group(function () {
            Route::patch('/reports/status/{id}/{action}', [PostController::class, 'changeReportStatus'])->name('reports.status');
        });
    });
});
