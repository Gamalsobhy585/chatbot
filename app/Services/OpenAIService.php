<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model');
        $this->baseUrl = config('services.openai.base_url');
    }

    /**
     * Send an image (and optional prompt) to OpenAI Vision and get a description.
     */
    public function describeImage(string $base64Image, ?string $prompt = null): string
    {
        $userPrompt = $prompt ?: 'Describe this product in detail for an e-commerce listing. '
            . 'Include likely category, key features, materials, colors, and a short marketing description.';

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $userPrompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,{$base64Image}",
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 700,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI Vision request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Failed to get a response from OpenAI: ' . $response->status());
        }

        return $response->json('choices.0.message.content', 'No description returned.');
    }

    /**
     * Send a plain text chat message (for follow-up questions without a new image).
     */
    public function chat(string $prompt, array $history = []): string
    {
        $messages = array_merge($history, [
            ['role' => 'user', 'content' => $prompt],
        ]);

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 500,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI Chat request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Failed to get a response from OpenAI: ' . $response->status());
        }

        return $response->json('choices.0.message.content', 'No response returned.');
    }
}