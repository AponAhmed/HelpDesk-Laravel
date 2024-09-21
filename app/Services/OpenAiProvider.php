<?php

namespace App\Services;

use App\Models\Option;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = Option::get('ai_api_key_openai', "", true);
        $this->model = Option::get('ai_model_openai', "gpt-3.5-turbo", true);
    }

    public function getResponse(string $prompt): string
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'max_tokens' => 100,
                ],
            ]);

            // Check if the status code is 200
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Error: ' . $response->getBody()->getContents());
            }

            $data = json_decode($response->getBody(), true);

            // Check if 'choices' array is present and has content
            if (isset($data['choices'][0]['text'])) {
                return $data['choices'][0]['text'];
            } else {
                throw new \Exception('Response does not contain valid choices');
            }
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error processing OpenAI response: ' . $e->getMessage());

            // Return a user-friendly error message or an empty string
            return 'An error occurred while processing the AI response.';
        }
    }
}
