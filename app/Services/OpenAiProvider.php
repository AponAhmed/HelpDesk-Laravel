<?php

namespace App\Services;

use GuzzleHttp\Client;

class OpenAiProvider implements AiProviderInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getResponse(string $prompt): string
    {
        $response = $this->client->post('https://api.openai.com/v1/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'max_tokens' => 100,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['choices'][0]['text'] ?? '';
    }
}
