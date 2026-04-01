<?php

use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\CheckAppKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsCompanyAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->namespace('App\Http\Controllers\Admin')
                ->name('admin.')
                ->group(base_path('routes/backoffice/admin.php'));
            Route::middleware('web')
                ->namespace('App\Http\Controllers\CompanyAdmin')
                ->name('company_admin.')
                ->prefix('company-admin')
                ->group(base_path('routes/backoffice/company_admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('admin.auth', [AdminAuthMiddleware::class]);
        $middleware->group('company_admin', [IsCompanyAdmin::class]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'check.app.key' => CheckAppKey::class,
            'set.lang' => \App\Http\Middleware\SetLangMiddleware::class,
            'check.permission' => \App\Http\Middleware\CheckPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        });
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                        'result' => [],
                        'code' => 422
                    ], 422);
                }

                return response()->json($e->errors(), 422);
            }
        });
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => trans('general.unauthenticated'),
                    'result' => [],
                    'code' => 401
                ], 401);
            }
        });
    })->create();
