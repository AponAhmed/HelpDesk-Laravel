<?php

namespace App\Providers;

use App\Models\Option;
use Illuminate\Support\ServiceProvider;
use App\Services\AiProviderInterface;
use App\Services\FreeBoxAiProvider;
use App\Services\OpenAiProvider;
use App\Services\GeminiAiProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register()
    {
        // You can dynamically bind the provider here based on conditions
        $this->app->bind(AiProviderInterface::class, function ($app) {
            $provider = Option::get('ai_provider', 'freebox', true);
            switch ($provider) {
                case 'gemini':
                    return new GeminiAiProvider();
                case 'freebox':
                    return new FreeBoxAiProvider();
                case 'openai':
                default:
                    return new OpenAiProvider(); // Default provider
            }
        });
    }
}
