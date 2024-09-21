<?php

namespace App\Services;

use App\Models\Option;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FreeBoxAiProvider implements AiProviderInterface
{
    protected $tone;
    protected $ai_lang;
    protected $url;

    public function __construct()
    {

        $this->tone = Option::get('ai_tone', 'Formal', true);
        $this->url = Option::get(
            'ai_freebox_model',
            'ai-content-generator',
            true
        );
        $this->ai_lang = Option::get('ai_lang', 'English', true);
    }

    public function getResponse(string $prompt): string
    {
        $url = 'https://api.aifreebox.com/api/openai';
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin' => 'https://your-origin.com'
        ];
        $data = [
            'language' => $this->ai_lang,
            'tone' => $this->tone,
            'url' => "open-ai", //$this->url,
            'prompt' => $prompt,
        ];

        $response = $this->sendPostRequest($url, $data, $headers);
        // Check if the response is valid and not an error message
        if (strpos($response, 'Error:') !== false) {
            // Log the error or handle it as needed
            // For example, you might want to throw an exception or log to a file
            Log::error('AI Provider Error: ' . $response);
            return "FreeBox AI Provider Error"; // Return null if there's an error
        }
        return $response;
    }

    function sendPostRequest($url, $data, $headers)
    {
        // Create a Guzzle client
        $client = new Client();
        try {
            // Send a POST request with the given URL, data, and headers
            $response = $client->post($url, [
                'json' => $data,  // Automatically encodes data to JSON
                'headers' => $headers
            ]);
            // Get the response body as a string
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            // If an error occurs, return the error message
            return 'Error: ' . $e->getMessage();
        }
    }
}
