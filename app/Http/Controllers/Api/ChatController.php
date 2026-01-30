<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function getSessions(Request $request)
    {
        $sessions = ChatSession::where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($sessions);
    }

    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'nullable|string|max:255',
        ]);

        $session = ChatSession::create([
            'id' => Str::uuid(),
            'user_id' => $request->user()->id,
            'topic' => $validated['topic'] ?? 'การสนทนาใหม่',
        ]);

        return response()->json($session, 201);
    }

    public function getMessages(Request $request, $sessionId)
    {
        $session = ChatSession::where('user_id', $request->user()->id)
            ->findOrFail($sessionId);

        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'session' => $session,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $session = ChatSession::where('user_id', $request->user()->id)
            ->findOrFail($sessionId);

        // Save user message
        $userMessage = ChatMessage::create([
            'id' => Str::uuid(),
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $validated['content'],
        ]);

        // Generate AI response (integrate with OpenAI)
        $aiResponse = $this->generateAIResponse($session, $validated['content']);

        // Save AI message
        $aiMessage = ChatMessage::create([
            'id' => Str::uuid(),
            'session_id' => $sessionId,
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        // Update session timestamp
        $session->touch();

        return response()->json([
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
        ]);
    }

    public function deleteSession(Request $request, $sessionId)
    {
        $session = ChatSession::where('user_id', $request->user()->id)
            ->findOrFail($sessionId);

        $session->delete();

        return response()->json(['message' => 'ลบการสนทนาสำเร็จ']);
    }

    private function generateAIResponse(ChatSession $session, string $userMessage): string
    {
        // Default response - integrate with OpenAI for real responses
        $user = $session->user;
        $zodiac = $user->thai_animal ?? 'มะโรง';

        $responses = [
            "สวัสดีค่ะ คุณเป็นคนปี{$zodiac} ดิฉันพร้อมให้คำปรึกษาเรื่องดวงชะตาค่ะ",
            "ตามดวงของคุณในปีนี้ จะมีโชคลาภเข้ามา โดยเฉพาะช่วงกลางปีค่ะ",
            "สำหรับเรื่องความรัก ดวงของคุณบ่งบอกว่าจะพบคนที่ใช่ในเร็วๆ นี้ค่ะ",
            "เรื่องการเงินในช่วงนี้ ควรระมัดระวังการใช้จ่ายนะคะ",
        ];

        return $responses[array_rand($responses)];
    }
}
