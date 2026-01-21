<?php

namespace App\Providers;

use App\Events\EventTriggered;
use App\Listeners\ProcessEventNotifications;
use App\Services\ChannelManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ChannelManager as a singleton
        $this->app->singleton(ChannelManager::class, function () {
            return new ChannelManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            EventTriggered::class,
            ProcessEventNotifications::class
        );
    }
}

