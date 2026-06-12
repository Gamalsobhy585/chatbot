<?php

namespace App\Repositories\Contracts;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function findOrCreateSession(string $sessionKey): ChatSession;

    public function addMessage(
        ChatSession $session,
        string $role,
        ?string $content,
        ?string $imagePath = null
    ): ChatMessage;

    public function getMessages(ChatSession $session): Collection;
}