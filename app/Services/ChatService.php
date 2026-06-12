<?php

namespace App\Services;

use App\DTOs\ChatMessageDTO;
use App\Models\ChatMessage;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ChatService
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository,
        private OpenAIService $openAIService,
    ) {}

    public function handleMessage(ChatMessageDTO $dto): ChatMessage
    {
        $session = $this->chatRepository->findOrCreateSession($dto->sessionKey);

        $imagePath = null;

        if ($dto->image) {
            $imagePath = $dto->image->store('chat-images', 'public');

            // store user message (with image)
            $this->chatRepository->addMessage(
                $session,
                'user',
                $dto->prompt,
                $imagePath
            );

            $base64 = base64_encode(file_get_contents($dto->image->getRealPath()));
            $aiResponse = $this->openAIService->describeImage($base64, $dto->prompt);
        } else {
            // store user message (text only)
            $this->chatRepository->addMessage(
                $session,
                'user',
                $dto->prompt
            );

            $history = $this->buildHistory($session);
            $aiResponse = $this->openAIService->chat($dto->prompt ?? '', $history);
        }

        return $this->chatRepository->addMessage(
            $session,
            'assistant',
            $aiResponse
        );
    }

    public function getConversation(string $sessionKey): Collection
    {
        $session = $this->chatRepository->findOrCreateSession($sessionKey);

        return $this->chatRepository->getMessages($session);
    }

    private function buildHistory($session): array
    {
        return $this->chatRepository->getMessages($session)
            ->filter(fn ($m) => $m->content !== null)
            ->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->take(-10) // last 10 messages for context
            ->values()
            ->toArray();
    }
}