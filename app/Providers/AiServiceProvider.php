<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiProviderInterface;
use App\Services\OpenAiProvider; // Default provider
use App\Services\GeminiAiProvider; // Optional other provider

class AiServiceProvider extends ServiceProvider
{
    public function register()
    {
        // You can dynamically bind the provider here based on conditions
        $this->app->bind(AiProviderInterface::class, function ($app) {
            $provider = config('ai.provider'); // get the provider name from config
            switch ($provider) {
                case 'gemini':
                    return new GeminiAiProvider();
                case 'openai':
                default:
                    return new OpenAiProvider(); // Default provider
            }
        });
    }
}
