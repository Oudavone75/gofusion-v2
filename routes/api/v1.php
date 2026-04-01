<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    CampaignSeasonController,
    CompanyController,
    ImageValidationStepController,
    EventValidationStepController,
    ChallengeStepController,
    SpinWheelValidationStepController,
    GoSessionController,
    CarbonFootprintController,
    NewsCategoryController,
    NewsController,
    QuizController,
    SurveyFeedbackController,
    UserController,
    WithdrawalRequestController,
    CompanyContactController,
    NotificationController,
    PostController
};

Route::group(['middleware' => 'set.lang'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/check-username', [AuthController::class, 'checkUsername']);
    Route::get('cities-list', [AuthController::class, 'getCitiesList']);
    Route::get('/session-time-duration-list', [AuthController::class, 'getSessionsDurationList']);
    Route::get('/lanaguages-list', [AuthController::class, 'getLanaguagesList']);
    Route::get('/modes-list', [AuthController::class, 'modesList']);

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::post('change-language', [AuthController::class, 'changeLanguage'])->middleware('auth:sanctum');
        Route::post('assing-user-mode', [AuthController::class, 'assignUserMode'])->middleware('auth:sanctum');
        Route::get('company-departments-list/{company_id}', [AuthController::class, 'getCompanyDepartments'])->middleware('auth:sanctum');
        Route::delete('delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');
        Route::post('update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
        Route::post('change-password', [AuthController::class, 'updatePassword'])->middleware('auth:sanctum');
        Route::post('update-activity', [AuthController::class, 'updateActivity'])->middleware('auth:sanctum');
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'company', 'as' => 'company.'], function () {
            Route::post('code-verification', [CompanyController::class, 'codeVerification'])->name('code-verification');
            Route::post('pending-company-assign-request', [CompanyController::class, 'pendingCompanyAssignRequest'])->name('pending-company-assign-request');
            Route::get('departments', [CompanyController::class, 'getCompanydepartments'])->name('departments');
        });

        Route::group(['prefix' => 'steps', 'as' => 'steps.'], function () {

            Route::group(['prefix' => 'quizzes', 'as' => 'quizzes.'], function () {
                Route::get('/{go_session_step_id}', [QuizController::class, 'getQuizStep']);
                Route::post('/{go_session_step_id}', [QuizController::class, 'attemptQuizStep']);
            });

            Route::group(['prefix' => 'image', 'as' => 'image.'], function () {
                Route::get('step-details', [ImageValidationStepController::class, 'getImageStepDetails'])->name('step-details');
                Route::post('upload-step', [ImageValidationStepController::class, 'uploadStepImage'])->name('upload-step');
                Route::post('validate-step', [ImageValidationStepController::class, 'validateStepImage'])->name('validate-step');
                Route::post('appeal-for-manual-validate', [ImageValidationStepController::class, 'appealForManualValidate']);
            });

            Route::group(['prefix' => 'event', 'as' => 'event.'], function () {
                Route::get('step-details', [EventValidationStepController::class, 'getEventStepDetails'])->name('step-details');
                Route::post('image-upload-step', [EventValidationStepController::class, 'uploadEventImage'])->name('image-upload-step');
                Route::post('image-validate-step', [EventValidationStepController::class, 'validateEvent'])->name('image-validate-step');
                Route::post('validate-later-event', [EventValidationStepController::class, 'validateEventLater']);
            });

            Route::group(['prefix' => 'challenges', 'as' => 'challenges.'], function () {
                Route::get('categories', [ChallengeStepController::class, 'getCategories'])->name('categories');
                Route::post('create', [ChallengeStepController::class, 'createChallengeStep'])->name('categories');
                Route::get('themes', [ChallengeStepController::class, 'getThemes'])->name('themes');
            });

            Route::group(['prefix' => 'spin-wheel', 'as' => 'spin-wheel.'], function () {
                Route::get('step-details', [SpinWheelValidationStepController::class, 'getSpinWheelStepDetails'])->name('step-details');
                Route::post('user-create', [SpinWheelValidationStepController::class, 'createSpinWheelSubmissionStep'])->name('user-create');
            });

            Route::group(['prefix' => 'survey-feedback', 'as' => 'survey-feedback.'], function () {
                Route::get('/{go_session_step_id}', [SurveyFeedbackController::class, 'getSurveyFeedback']);
                Route::post('/{go_session_step_id}', [SurveyFeedbackController::class, 'submitSurveyStep']);
            });
        });

        Route::group(['prefix' => 'inspiration-challenges', 'as' => 'inspiration-challenges.'], function () {
            Route::get('themes', [ChallengeStepController::class, 'getThemes'])->name('themes');
            Route::get('listing', [ChallengeStepController::class, 'getThemeChallengesListing'])->name('listing');
            Route::get('detail/{challenge_step_id}', [ChallengeStepController::class, 'getThemeChallengeDetail'])->name('Detail');
            Route::post('upload-image', [ChallengeStepController::class, 'uploadChallengeImage'])->name('upload-image');
            Route::post('validate', [ChallengeStepController::class, 'validateChallenge'])->name('validate');
            Route::post('create', [ChallengeStepController::class, 'createInspirationChallenge'])->name('create');
        });

        Route::get('get-active-campaign-or-season', [CampaignSeasonController::class, 'getActiveCampaignOrSeason']);
        Route::get('user-progress', [UserController::class, 'getUserProgress'])->name('user-progress');
        Route::get('user-carbon-foot-print', [CarbonFootprintController::class, 'getCurrentMonthCarbonFootprint'])->name('user-carbon-foot-print');
        Route::post('save-carbons', [CarbonFootprintController::class, 'saveCarbonFootprints'])->name('save-carbons');
        Route::get('user-scores', [AuthController::class, 'getUserScores']);
        Route::get('user-details', [AuthController::class, 'getUserDetails']);
        Route::get('user-level-details', [AuthController::class, 'getUserLevelDetails']);
        Route::get('/ranking-leader-board', [CampaignSeasonController::class, 'getLeaderBoard']);
        Route::post('/create-withdrawal-request', [WithdrawalRequestController::class, 'createWithdrawalRequest']);
        Route::get('/withdrawal-requests', [WithdrawalRequestController::class, 'getWithdrawalRequests']);
        Route::get('get-mention-users-list', [UserController::class, 'getMentionUsersList'])->name('get-users-list');
        Route::get('invite-friends-list', [UserController::class, 'getInviteFriendsList'])->middleware('auth:sanctum');
        Route::post('company-contact', [CompanyContactController::class, 'saveCompanyContact'])->name('company-contact');

        // Post Management Routes
        Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
            Route::post('create', [PostController::class, 'createPost'])->name('create');
            Route::get('list', [PostController::class, 'getPostsList'])->name('list');
            Route::get('detail/{post_id}', [PostController::class, 'getPostDetail'])->name('detail');
            Route::get('user-list', [PostController::class, 'getUserPostsList'])->name('users.list');
            Route::post('{post_id}/update', [PostController::class, 'updatePost'])->name('update');
            Route::delete('{post_id}/delete', [PostController::class, 'deletePost'])->name('delete');
            Route::post('add-comment', [PostController::class, 'addComment'])->name('add-comment');
            Route::post('react', [PostController::class, 'reactToPost'])->name('react');
            Route::post('report', [PostController::class, 'reportPost'])->name('report');
            Route::delete('delete-post-media/{media_id}', [PostController::class, 'deletePostMedia'])->name('delete-post-media');
            Route::post('like-comment', [PostController::class, 'likeComment'])->name('like-comment');
        });

        Route::post('/encrypt-id', [UserController::class, 'encryptId'])->name('encrypt-id');
        Route::post('/decrypt-id', [UserController::class, 'decryptId'])->name('decrypt-id');
        });

        Route::group(['prefix' => 'sessions', 'middleware' => 'auth:sanctum', 'as' => 'sessions.'], function () {
            Route::get('session-progress/{campaign_season_id}', [GoSessionController::class, 'getSessionProgress']);
            Route::get('list/{campaign_season_id}', [GoSessionController::class, 'getGoSessions']);
            Route::get('/get-session-steps/{go_session_id}', [GoSessionController::class, 'getSessionStepsList']);
            Route::get('/session-time-duration-list', [GoSessionController::class, 'getSessionTimeDurations']);
            Route::post('/update-session-time-duration', [GoSessionController::class, 'updateSessionTimeDuration']);
            Route::get('/{id}', [GoSessionController::class, 'getGoSessionDetails'])->name('details');
    });

    Route::group(['prefix' => 'news', 'middleware' => 'auth:sanctum', 'as' => 'news.'], function () {
        Route::get('category-list', [NewsCategoryController::class, 'getNewsCategoryList']);
        Route::get('list', [NewsController::class, 'getNewsList']);
        Route::get('detail/{news_id}', [NewsController::class, 'getNewsDetail']);
    });

    Route::group(['prefix' => 'notifications', 'middleware' => 'auth:sanctum', 'as' => 'notifications.'], function () {
        Route::get('/list', [NotificationController::class, 'getNotificationsList']);
        Route::post('/test', [NotificationController::class, 'test']);
    });
    Route::post('/save-carbon-footprint-values/webhook', [CarbonFootprintController::class, 'saveCarbonFootprintValues']);
    Route::group(['prefix' => 'user-leaves', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/list', [UserController::class, 'getUserLeavesList']);
    });
});
