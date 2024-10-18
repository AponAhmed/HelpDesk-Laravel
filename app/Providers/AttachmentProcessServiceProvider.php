<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

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
        // Get the configured attachment directory path
        $attachmentDirectory = storage_path('app/public/' . config('attachment.filtered_attachment_path'));

        // Check if the directory exists, and create it if it doesn't
        if (!File::exists($attachmentDirectory)) {
            File::makeDirectory($attachmentDirectory, 0755, true, true);
        }
    }
}
