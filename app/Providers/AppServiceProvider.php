<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\{StoreCompleteUserSessionEvent, UserProgressEvent, UserScoreEvent};
use App\Listeners\{StoreCompleteUserSessionListner, UserProgressListener, UserScoreListener};
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        Event::listen(
            UserProgressEvent::class,
            UserProgressListener::class
        );
        Event::listen(
            UserScoreEvent::class,
            UserScoreListener::class
        );
        Event::listen(
            StoreCompleteUserSessionEvent::class,
            StoreCompleteUserSessionListner::class
        );
    }
}
