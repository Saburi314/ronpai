<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChatHistoryService;
use App\Services\AiService;
use App\Services\SessionService;
use App\Constants\Opponents;

class DebateApiController extends Controller
{
    private $chatHistoryService;
    private $aiService;
    private $sessionService;

    public function __construct(ChatHistoryService $chatHistoryService, AiService $aiService, SessionService $sessionService)
    {
        $this->chatHistoryService = $chatHistoryService;
        $this->aiService = $aiService;
        $this->sessionService = $sessionService;
    }

    /**
     * 🔹 AIのレスポンスを取得
     */
    public function getAiResponse(Request $request)
    {
        $userMessage = $request->input('message');
        $opponentKey = $request->input('opponentKey', Opponents::DEFAULT);
        $sessionId = session()->getId();

        $messages = $this->chatHistoryService->addUserMessage($sessionId, $userMessage);
        $aiMessage = $this->aiService->getAiResponse($messages, $opponentKey);
        $this->chatHistoryService->addAiMessage($sessionId, $aiMessage);

        return response()->json(['response' => $aiMessage]);
    }

    /**
     * 🔹 チャット履歴を取得
     */
    public function getChatHistory(Request $request)
    {
        $sessionId = session()->getId();
        $chatHistory = $this->chatHistoryService->getChatHistory($sessionId);

        return response()->json(['history' => $chatHistory->messages ?? []]);
    }

    /**
     * 🔹 チャット履歴をリセット
     */
    public function resetChatHistory()
    {
        $this->sessionService->invalidateSession();
        
        return response()->json([
            'message' => 'ディベートのセッションをリセットしました。',
            'csrf_token' => csrf_token()
        ]);    }
}
