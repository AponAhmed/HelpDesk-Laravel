<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AttachmentProcessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        // Bind the AttachmentProcessor class to the service container
        $this->app->bind('attachment.processor', function ($app) {
            return new \App\Services\AttachmentProcessor();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
