<?php

namespace App\Services;

interface AiProviderInterface
{
    public function getResponse(string $prompt): string;
}
