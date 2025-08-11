<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('telegramBot', function ($app) {
            return new \App\Services\TelegramBot();
        });

        if ($this->app->environment(['local', 'testing']) && 
            class_exists(\Laravel\Pail\PailServiceProvider::class)) {
            $this->app->register(\Laravel\Pail\PailServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
