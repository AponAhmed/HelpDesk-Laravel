<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Services\AiProviderInterface;
use Illuminate\Http\Request;

class AiService extends Controller
{
    protected $aiProvider;

    public function __construct(AiProviderInterface $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    public static function getAiSettings()
    {
        $provider = Option::get('ai_provider', 'freebox', true);
        $creativity = Option::get('ai_temperature', '0.7', true);
        $tone = Option::get('ai_tone', 'Formal', true);
        return [
            'provider' => $provider,
            'creativity' => $creativity,
            'tone' => $tone,
        ];
    }

    public function generate(Request $request)
    {
        // Validate the 'prompt' input
        $validated = $request->validate([
            'prompt' => 'required|string|min:5|max:500', // Adjust min/max based on your requirements
        ]);

        // // Get the validated 'prompt' from the request
        $prompt = $validated['prompt'];
        //$prompt = "Write a paragraph about journy by boat";

        // // Call the AI provider with the validated prompt
        $response = $this->aiProvider->getResponse($prompt);
        //dd($response);
        //Return the response (you can adjust this based on your needs)
        return response()->json(['response' => $response]);
    }
}
