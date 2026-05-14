<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentEmbedding;
use App\Models\AiChat;
use App\Models\Document;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AiChatController extends Controller
{
    public function __construct(
        private readonly AIService $aiService,
    ) {}

    // ─── Page ─────────────────────────────────────────────────────────────────

    /**
     * Halaman utama AI Assistant (global chat).
     */
    public function index()
    {
        $user  = Auth::user();
        $chats = AiChat::where('user_id', $user->id)
            ->latest('last_activity_at')
            ->limit(30)
            ->get(['id', 'title', 'mode', 'last_activity_at', 'document_id']); // ← mode bukan context_type

        return view('ai.index', compact('chats'));
    }

    /**
     * Halaman AI Assistant dengan konteks dokumen spesifik.
     */
    public function documentContext(Document $document)
    {
        $this->authorize('view', $document);

        // Pastikan dokumen sudah di-embed
        $version = $document->currentVersion;
        if ($version && $version->embedding_status === 'pending') {
            ProcessDocumentEmbedding::dispatch($version->id);
        }

        $chats = AiChat::where('user_id', Auth::id())
            ->where('document_id', $document->id)
            ->latest()
            ->limit(10)
            ->get(['id', 'title', 'last_activity_at']);

        return view('ai.document-context', compact('document', 'chats'));
    }

    // ─── Chat Session ─────────────────────────────────────────────────────────

    /**
     * Buat chat session baru.
     * POST /ai/chats
     */
    public function createChat(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'document_id' => 'nullable|integer|exists:documents,id',
        ]);

        $user       = Auth::user();
        $documentId = $request->document_id;

        if ($documentId) {
            $doc = Document::findOrFail($documentId);
            $this->authorize('view', $doc);
        }

        $title = $request->title
            ?? ($documentId ? 'Chat - ' . optional(Document::find($documentId))->title : 'Chat Baru');

        $chat = $this->aiService->createChat($user, $title, $documentId);

        return response()->json([
            'success' => true,
            'chat'    => [
                'id'          => $chat->id,
                'title'       => $chat->title,
                'mode'        => $chat->mode,          // ← mode
                'document_id' => $chat->document_id,
            ],
        ]);
    }

    /**
     * Ambil daftar pesan dalam satu chat.
     * GET /ai/chats/{chat}
     */
    public function showChat(AiChat $chat): JsonResponse
    {
        $this->authorizeChat($chat);

        $messages = $this->aiService->getChatHistory($chat);

        return response()->json([
            'chat'     => $chat->only(['id', 'title', 'mode', 'document_id']),
            'messages' => $messages->map(fn ($m) => [
                'id'          => $m->id,
                'role'        => $m->role,
                'content'     => $m->content,
                'sources'     => $m->sources ?? [],   // ← sources bukan citations
                'tokens_used' => $m->tokens_used,
                'created_at'  => $m->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Hapus chat.
     * DELETE /ai/chats/{chat}
     */
    public function deleteChat(AiChat $chat): JsonResponse
    {
        $this->authorizeChat($chat);
        $chat->messages()->delete();
        $chat->delete();

        return response()->json(['success' => true]);
    }

    // ─── Send Message ─────────────────────────────────────────────────────────

    /**
     * Kirim pesan ke AI.
     * POST /ai/chats/{chat}/messages
     */
    public function sendMessage(Request $request, AiChat $chat): JsonResponse
    {
        $this->authorizeChat($chat);

        $request->validate([
            'message'     => 'required|string|max:2000',
            'document_id' => 'nullable|integer|exists:documents,id',
        ]);

        $user    = Auth::user();
        $message = trim($request->message);

        // Rate limiting: max 30 pesan per menit per user
        $rateLimitKey = 'ai-chat:' . $user->id;
        $maxPerMinute = (int) config('ai.rate_limit', 30);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxPerMinute)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        $documentId = $request->document_id ?? $chat->document_id;

        if ($documentId) {
            $doc = Document::find($documentId);
            if (! $doc) {
                return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan.'], 404);
            }
            $this->authorize('view', $doc);
        }

        $aiMessage = $this->aiService->chat($chat, $message, $user, $documentId);

        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $aiMessage->id,
                'role'       => $aiMessage->role,
                'content'    => $aiMessage->content,
                'sources'    => $aiMessage->sources ?? [],   // ← sources
                'created_at' => $aiMessage->created_at->toISOString(),
            ],
        ]);
    }

    // ─── Summary & Compare ────────────────────────────────────────────────────

    /**
     * Ringkas dokumen via AI.
     * POST /ai/documents/{document}/summarize
     */
    public function summarize(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $result = $this->aiService->summarize($document, Auth::user());

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Bandingkan dua versi dokumen.
     * POST /ai/documents/{document}/compare-versions
     */
    public function compareVersions(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $request->validate([
            'old_version_id' => 'required|integer|exists:document_versions,id',
            'new_version_id' => 'required|integer|exists:document_versions,id',
        ]);

        $oldVersion = $document->versions()->findOrFail($request->old_version_id);
        $newVersion = $document->versions()->findOrFail($request->new_version_id);

        $result = $this->aiService->compareVersions(
            $document, $oldVersion, $newVersion, Auth::user()
        );

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function authorizeChat(AiChat $chat): void
    {
        if ($chat->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke chat ini.');
        }
    }
}