<?php

namespace App\Services\AI;

use App\Models\AiChat;
use App\Models\AiLog;
use App\Models\AiMessage;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    private string $chatModel;
    private int    $maxTokens;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Anda adalah AI Assistant untuk sistem document control perusahaan.

Aturan yang WAJIB diikuti:
1. Jawab HANYA berdasarkan dokumen yang tersedia dalam konteks.
2. JANGAN mengarang atau mengasumsikan informasi yang tidak ada dalam konteks.
3. Jika informasi tidak ditemukan dalam dokumen, katakan dengan jelas: "Informasi tidak ditemukan dalam dokumen yang tersedia."
4. Selalu sebutkan sumber dokumen (nama dokumen dan halaman) saat memberikan jawaban.
5. Gunakan Bahasa Indonesia yang formal dan profesional.
6. Ikuti permission dan RBAC system — jangan bocorkan dokumen di luar akses user.
7. Untuk pertanyaan yang bersifat operasional sistem (bukan isi dokumen), arahkan ke administrator.

Format jawaban:
- Jawaban langsung dan ringkas
- Jika ada poin penting, gunakan bullet point
- Akhiri dengan bagian [Sumber] yang mereferensikan dokumen dan halaman
PROMPT;

    public function __construct(
        private readonly RAGService $ragService,
    ) {
        $this->chatModel = config('ai.chat_model', 'gpt-4o-mini');
        $this->maxTokens = (int) config('ai.max_tokens', 1500);
    }

    // ─── CHAT ────────────────────────────────────────────────────────────────

    public function chat(
        AiChat $chat,
        string $userMessage,
        User $user,
        ?int $documentId = null,
    ): AiMessage {
        $startTime = microtime(true);

        // 1. Simpan pesan user
        AiMessage::create([
            'ai_chat_id' => $chat->id,
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        // Auto-generate judul dari pesan pertama
        if ($chat->messages()->count() === 1) {
            $chat->generateTitle($userMessage);
        }

        try {
            // 2. Retrieve context via RAG
            $chunks    = $this->ragService->retrieve($userMessage, $user, $documentId);
            $context   = $this->ragService->buildContext($chunks);
            $sources   = $this->ragService->buildCitations($chunks); // → disimpan di kolom 'sources'

            // 3. Build messages untuk OpenAI
            $messages = $this->buildChatMessages($chat, $userMessage, $context);

            // 4. Panggil LLM
            $response = OpenAI::chat()->create([
                'model'       => $this->chatModel,
                'messages'    => $messages,
                'max_tokens'  => $this->maxTokens,
                'temperature' => 0.2,
            ]);

            $aiContent     = $response->choices[0]->message->content;
            $tokensUsed    = $response->usage->totalTokens ?? 0;
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            // 5. Simpan pesan AI — pakai kolom sesuai model AiMessage
            $aiMsg = AiMessage::create([
                'ai_chat_id'      => $chat->id,
                'role'            => 'assistant',
                'content'         => $aiContent,
                'sources'         => $sources,          // ← sesuai model (bukan citations)
                'tokens_used'     => $tokensUsed,
                'response_time_ms' => $responseTimeMs,  // ← sesuai model
            ]);

            // 6. Audit log — sesuai kolom AiLog
            AiLog::record(
                action: 'chat',
                query: substr($userMessage, 0, 500),
                documentsRetrieved: $chunks->count(),
                tokensUsed: $tokensUsed,
                responseTimeMs: $responseTimeMs,
                status: 'success',
                aiChatId: $chat->id,
            );

            return $aiMsg;

        } catch (\Throwable $e) {
            Log::error('AIService::chat error', [
                'user_id' => $user->id,
                'chat_id' => $chat->id,
                'error'   => $e->getMessage(),
            ]);

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $errorMsg = AiMessage::create([
                'ai_chat_id'       => $chat->id,
                'role'             => 'assistant',
                'content'          => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda. Silakan coba lagi.',
                'response_time_ms' => $responseTimeMs,
            ]);

            AiLog::record(
                action: 'chat',
                query: substr($userMessage, 0, 500),
                status: 'failed',
                errorMessage: $e->getMessage(),
                aiChatId: $chat->id,
            );

            return $errorMsg;
        }
    }

    // ─── SUMMARY ─────────────────────────────────────────────────────────────

    public function summarize(Document $document, User $user): array
    {
        $query  = "Ringkas keseluruhan isi dokumen ini secara komprehensif.";
        $chunks = $this->ragService->retrieve($query, $user, $document->id);

        if ($chunks->isEmpty()) {
            return [
                'summary'    => 'Dokumen tidak dapat diakses atau belum diproses untuk AI.',
                'key_points' => [],
                'sources'    => [],
            ];
        }

        $context = $this->ragService->buildContext($chunks);

        $response = OpenAI::chat()->create([
            'model'       => $this->chatModel,
            'messages'    => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user',   'content' => $this->buildSummaryPrompt($context, $document->title)],
            ],
            'max_tokens'  => 2000,
            'temperature' => 0.1,
        ]);

        $raw = $response->choices[0]->message->content;
        $tokensUsed = $response->usage->totalTokens ?? 0;

        AiLog::record(
            action: 'summarize',
            query: "summarize:{$document->id}",
            documentsRetrieved: $chunks->count(),
            tokensUsed: $tokensUsed,
        );

        return [
            'summary'    => $raw,
            'key_points' => $this->extractKeyPoints($raw),
            'sources'    => $this->ragService->buildCitations($chunks),
        ];
    }

    // ─── VERSION COMPARE ─────────────────────────────────────────────────────

    public function compareVersions(
        Document $document,
        DocumentVersion $oldVersion,
        DocumentVersion $newVersion,
        User $user,
    ): array {
        $query   = "Apa perbedaan dan perubahan antara versi ini?";
        $results = $this->ragService->retrieveForVersionCompare(
            $query, $user, $document->id, $oldVersion->id, $newVersion->id
        );

        $oldContext = $this->ragService->buildContext($results['old']);
        $newContext = $this->ragService->buildContext($results['new']);

        if (empty($oldContext) || empty($newContext)) {
            return [
                'changes'     => 'Salah satu versi tidak memiliki data yang cukup untuk dibandingkan.',
                'summary'     => '',
                'old_version' => $oldVersion->version_number,
                'new_version' => $newVersion->version_number,
            ];
        }

        $response = OpenAI::chat()->create([
            'model'       => $this->chatModel,
            'messages'    => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user',   'content' => $this->buildComparePrompt(
                    $document->title,
                    $oldVersion->version_number,
                    $newVersion->version_number,
                    $oldContext,
                    $newContext,
                )],
            ],
            'max_tokens'  => 2000,
            'temperature' => 0.1,
        ]);

        $raw = $response->choices[0]->message->content;
        $tokensUsed = $response->usage->totalTokens ?? 0;

        AiLog::record(
            action: 'version_compare',
            query: "compare:{$oldVersion->version_number}→{$newVersion->version_number}",
            tokensUsed: $tokensUsed,
        );

        return [
            'changes'     => $raw,
            'summary'     => $this->extractSummaryLine($raw),
            'old_version' => $oldVersion->version_number,
            'new_version' => $newVersion->version_number,
        ];
    }

    // ─── MANAGE CHAT ─────────────────────────────────────────────────────────

    public function createChat(User $user, string $title = 'Chat Baru', ?int $documentId = null): AiChat
    {
        return AiChat::create([
            'user_id'     => $user->id,
            'title'       => $title,
            'document_id' => $documentId,
            'mode'        => $documentId ? 'document' : 'global', // ← sesuai model (bukan context_type)
        ]);
    }

    public function getChatHistory(AiChat $chat, int $limit = 50): Collection
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    // ─── Build Prompts ────────────────────────────────────────────────────────

    private function buildChatMessages(AiChat $chat, string $userMessage, string $context): array
    {
        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
        ];

        // History percakapan (max 10 terakhir, skip error)
        $history = $chat->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        // Context RAG + pertanyaan
        if (! empty($context)) {
            $messages[] = [
                'role'    => 'user',
                'content' => "Konteks dokumen yang relevan:\n\n{$context}\n\n---\n\nPertanyaan: {$userMessage}",
            ];
        } else {
            $messages[] = [
                'role'    => 'user',
                'content' => "Pertanyaan: {$userMessage}\n\n(Tidak ditemukan dokumen relevan dengan pertanyaan ini.)",
            ];
        }

        return $messages;
    }

    private function buildSummaryPrompt(string $context, string $docTitle): string
    {
        return <<<PROMPT
Dokumen: {$docTitle}

Isi dokumen:
{$context}

Tugas:
1. Buat ringkasan komprehensif dokumen ini (2-3 paragraf)
2. Daftar 5-7 poin penting utama
3. Sebutkan sumber halaman untuk setiap poin

Format output:
## Ringkasan
[ringkasan di sini]

## Poin Penting
- [poin 1] (Hal. X)
- [poin 2] (Hal. X)

## Sumber
[daftar halaman yang digunakan]
PROMPT;
    }

    private function buildComparePrompt(
        string $docTitle,
        string $oldVer,
        string $newVer,
        string $oldContext,
        string $newContext,
    ): string {
        return <<<PROMPT
Dokumen: {$docTitle}

=== VERSI LAMA ({$oldVer}) ===
{$oldContext}

=== VERSI BARU ({$newVer}) ===
{$newContext}

Tugas: Analisis perbedaan antara kedua versi.

Format output:
## Ringkasan Perubahan
[1-2 kalimat ringkasan]

## Penambahan Baru
- [item baru di versi baru]

## Penghapusan / Perubahan
- [item yang dihapus atau diubah]

## Perubahan Signifikan
- [perubahan penting yang perlu diperhatikan]
PROMPT;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function extractKeyPoints(string $text): array
    {
        $lines  = explode("\n", $text);
        $points = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '- ') || str_starts_with($line, '• ')) {
                $points[] = ltrim($line, '-• ');
            }
        }

        return array_values(array_filter($points));
    }

    private function extractSummaryLine(string $text): string
    {
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if (! empty($line) && ! str_starts_with($line, '#')) {
                return $line;
            }
        }
        return '';
    }
}