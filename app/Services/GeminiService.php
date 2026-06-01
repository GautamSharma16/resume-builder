<?php

namespace App\Services;

use App\Support\GeminiHttp;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public const DEFAULT_FALLBACK_MODELS = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-1.5-flash',
    ];

    public const BUSY_MESSAGE = 'AI is temporarily busy. Please try again in a moment.';

    /**
     * @param  array<string, mixed>  $options  temperature, maxOutputTokens, topP, topK, timeout, responseMimeType
     * @return array{success:bool,text?:string,message?:string,model?:string,status?:int}
     */
    public function generateContent(string $prompt, array $options = []): array
    {
        $key = trim((string) config('services.gemini.key', ''));
        if ($key === '') {
            return ['success' => false, 'message' => 'Gemini API key not configured.'];
        }

        $generationConfig = [
            'temperature'     => (float) ($options['temperature'] ?? config('services.gemini.temperature', 0.2)),
            'topP'            => (float) ($options['topP'] ?? 0.75),
            'maxOutputTokens' => (int) ($options['maxOutputTokens'] ?? config('services.gemini.max_output_tokens', 1200)),
        ];

        if (isset($options['topK'])) {
            $generationConfig['topK'] = (int) $options['topK'];
        }
        if (isset($options['responseMimeType']) && is_string($options['responseMimeType']) && $options['responseMimeType'] !== '') {
            $generationConfig['responseMimeType'] = $options['responseMimeType'];
        }

        $timeout = (int) ($options['timeout'] ?? 60);
        $models  = $this->modelChain();

        foreach ($models as $index => $model) {
            $lastStatus = 0;
            $lastBody   = '';

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                if ($attempt > 1) {
                    usleep((2 ** $attempt) * 1_000_000);
                }

                try {
                    $response = $this->request($key, $model, $prompt, $generationConfig, $timeout);
                } catch (\Throwable $e) {
                    $lastBody = $e->getMessage();
                    Log::warning('Gemini request exception', [
                        'model'   => $model,
                        'attempt' => $attempt,
                        'error'   => $lastBody,
                    ]);

                    if ($attempt >= 3) {
                        break;
                    }

                    continue;
                }

                $lastStatus = $response->status();
                $lastBody   = $response->body();

                if ($response->successful()) {
                    $text = trim((string) Arr::get($response->json(), 'candidates.0.content.parts.0.text', ''));
                    if ($text !== '') {
                        if ($index > 0) {
                            Log::info("Fallback model success: {$model}");
                        }

                        return [
                            'success' => true,
                            'text'    => $text,
                            'model'   => $model,
                        ];
                    }

                    $lastBody = 'Empty response from AI.';
                }

                if (! $this->isRetryable($lastStatus, $lastBody)) {
                    break;
                }
            }

            Log::warning("Gemini model failed: {$model}", [
                'status' => $lastStatus,
                'body'   => mb_substr($lastBody, 0, 500),
            ]);
        }

        return [
            'success' => false,
            'message' => self::BUSY_MESSAGE,
            'status'  => $lastStatus ?? 0,
        ];
    }

    /**
     * @return list<string>
     */
    public function modelChain(): array
    {
        $configured = config('services.gemini.fallback_models');
        $models     = is_array($configured) && $configured !== []
            ? $configured
            : self::DEFAULT_FALLBACK_MODELS;

        $primary = trim((string) config('services.gemini.model', ''));
        if ($primary !== '') {
            $models = array_values(array_unique(array_merge([$primary], $models)));
        }

        return $models;
    }

    private function request(
        string $key,
        string $model,
        string $prompt,
        array $generationConfig,
        int $timeout
    ): Response {
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        return GeminiHttp::request($timeout)
            ->post(
                "{$baseUrl}/models/{$model}:generateContent?key=".urlencode($key),
                [
                    'contents'         => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => $generationConfig,
                ]
            );
    }

    private function isRetryable(int $status, string $body): bool
    {
        if (in_array($status, [429, 503, 502, 504], true)) {
            return true;
        }

        $haystack = strtoupper($body);

        foreach (['UNAVAILABLE', 'RESOURCE_EXHAUSTED', 'HIGH DEMAND', 'OVERLOADED', 'RATE LIMIT'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
