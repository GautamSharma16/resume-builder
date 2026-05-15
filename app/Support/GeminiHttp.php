<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeminiHttp
{
    public static function request(int $timeout = 90): PendingRequest
    {
        return Http::timeout($timeout)
            ->connectTimeout(20)
            ->withOptions([
                // Guzzle inherits HTTP_PROXY/HTTPS_PROXY from the machine. On this
                // Windows dev box they point at 127.0.0.1:9, which breaks Gemini.
                'proxy' => [],
            ]);
    }
}
