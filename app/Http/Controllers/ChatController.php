<?php

namespace App\Http\Controllers;

use App\DTOs\ChatMessageDTO;
use App\Http\Requests\ChatMessageRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    public function store(ChatMessageRequest $request): JsonResponse
    {
        $dto = ChatMessageDTO::fromArray([
            'session_key' => $request->input('session_key'),
            'prompt' => $request->input('prompt'),
            'image' => $request->file('image'),
        ]);

        $assistantMessage = $this->chatService->handleMessage($dto);

        return response()->json([
            'message' => $assistantMessage,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'string', 'max:100'],
        ]);

        $messages = $this->chatService->getConversation(
            $request->query('session_key')
        );

        return response()->json([
            'messages' => $messages,
        ]);
    }
}