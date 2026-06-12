<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class ChatMessageDTO
{
    public function __construct(
        public readonly string $sessionKey,
        public readonly ?string $prompt = null,
        public readonly ?UploadedFile $image = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionKey: $data['session_key'],
            prompt: $data['prompt'] ?? null,
            image: $data['image'] ?? null,
        );
    }
}