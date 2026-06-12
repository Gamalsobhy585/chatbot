<?php

namespace App\Repositories;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    public function findOrCreateSession(string $sessionKey): ChatSession
    {
        return ChatSession::firstOrCreate(
            ['session_key' => $sessionKey]
        );
    }

    public function addMessage(
        ChatSession $session,
        string $role,
        ?string $content,
        ?string $imagePath = null
    ): ChatMessage {
        return $session->messages()->create([
            'role' => $role,
            'content' => $content,
            'image_path' => $imagePath,
        ]);
    }

    public function getMessages(ChatSession $session): Collection
    {
        return $session->messages()
            ->orderBy('created_at')
            ->get();
    }
}