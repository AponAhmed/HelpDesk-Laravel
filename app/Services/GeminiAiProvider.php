<?php

namespace App\Services;

use App\Models\Option;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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
        $apiKey = Option::get('ai_api_key', "", true);
        $model = Option::get('ai_data_model', 'gemini-pro', true);

        try {
            $response = $this->httpClient->post('https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent', [
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ],
                        ]
                    ]
                ],
                'headers' => [
                    'x-goog-api-key' =>  $apiKey,
                    'x-goog-api-client' => 'genai-js/0.3.1',
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Check if the status code is 200
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Error: ' . $response->getBody()->getContents());
            }

            // Decode the response JSON
            $data = json_decode($response->getBody()->getContents(), true);

            // Check if 'candidates' is present and contains data
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                // Return the content text
                return $data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                throw new \Exception('Content text is not available in the response');
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error processing Gemini AI response: ' . $e->getMessage());

            // Return an error message
            return 'An error occurred while processing the AI response.';
        }
    }
}
