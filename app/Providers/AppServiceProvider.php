<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Notifications\Dispatcher as DispatcherContract;
use Illuminate\Notifications\ChannelManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding fallback: pastikan interface Dispatcher ter-resolve ke ChannelManager
        // (ini seharusnya sudah ter-bind oleh framework; ini hanya safety net sementara)
        $this->app->bind(DispatcherContract::class, function ($app) {
            return $app->make(ChannelManager::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
