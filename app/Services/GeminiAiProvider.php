<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeminiAiProvider implements AiProviderInterface
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function getResponse(string $prompt): string
    {
        // Replace with your Gemini API key
        $apiKey = 'YOUR_API_KEY';

        $response = $this->httpClient->post('https://api.gemini.google.com/v1/text/generate', [
            'json' => ['prompt' => $prompt],
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (!$response->getStatusCode() === 200) {
            throw new \Exception('Error: ' . $response->getBody()->getContents());
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['text'];
    }
}
