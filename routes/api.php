<?php

use App\Http\Controllers\Api\AppVersionController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/v1.php';
});

Route::post('create-event', [EventController::class, 'createEvent'])->middleware('check.app.key');

Route::prefix('app-versions')->group(function () {
    Route::get('/', [AppVersionController::class, 'getAllVersionsListing']);
    Route::post('/{platform}', [AppVersionController::class, 'updatePlatform']);
});
