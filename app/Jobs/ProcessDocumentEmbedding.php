<?php

namespace App\Jobs;

use App\Models\AiLog;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\DocumentVersion;
use App\Services\AI\DocumentParserService;
use App\Services\AI\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDocumentEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $documentVersionId,
        public readonly bool $forceReprocess = false,
    ) {}

    public function handle(
        DocumentParserService $parser,
        EmbeddingService $embedder,
    ): void {
        $version = DocumentVersion::with('document')->find($this->documentVersionId);

        if (! $version) {
            Log::warning('ProcessDocumentEmbedding: DocumentVersion tidak ditemukan', [
                'document_version_id' => $this->documentVersionId,
            ]);
            return;
        }

        $document = $version->document;

        // Skip jika dokumen tidak boleh diakses AI
        if (! $document->allow_ai_access) {
            Log::info('ProcessDocumentEmbedding: dokumen tidak allow_ai_access, skip', [
                'document_id' => $document->id,
            ]);
            return;
        }

        // Skip jika sudah ada chunk dan tidak diminta reprocess
        $existingDone = DocumentChunk::where('document_version_id', $version->id)
            ->where('embedding_status', 'done')
            ->count();

        if ($existingDone > 0 && ! $this->forceReprocess) {
            Log::info('ProcessDocumentEmbedding: sudah ada embedding, skip', [
                'version_id' => $version->id,
            ]);
            return;
        }

        $this->setStatus($version, 'processing');

        try {
            // Step 1: Parse
            $parsed = $parser->parse($version);

            if ($parsed['error']) {
                throw new \RuntimeException("Parse error: {$parsed['error']}");
            }

            if (empty(trim($parsed['text']))) {
                throw new \RuntimeException("Dokumen tidak menghasilkan teks.");
            }

            // Step 2: Chunk + Embed
            $savedCount = $embedder->processVersion($version, $parsed['text']);

            if ($savedCount === 0) {
                throw new \RuntimeException("Tidak ada chunk yang berhasil disimpan.");
            }

            // Step 3: Mark done
            $this->setStatus($version, 'done');
            $document->update(['ai_processed_at' => now()]);

            Log::info('ProcessDocumentEmbedding: selesai', [
                'version_id'   => $version->id,
                'saved_chunks' => $savedCount,
                'pages'        => $parsed['pages'],
            ]);

            $this->auditLog($document, $version, 'embedding_done', [
                'chunks_saved' => $savedCount,
                'pages'        => $parsed['pages'],
            ]);

        } catch (\Throwable $e) {
            $this->setStatus($version, 'failed');

            Log::error('ProcessDocumentEmbedding: gagal', [
                'version_id' => $version->id,
                'error'      => $e->getMessage(),
                'attempt'    => $this->attempts(),
            ]);

            $this->auditLog($document, $version, 'embedding_failed', [
                'error' => $e->getMessage(),
            ], 'error');

            throw $e; // trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessDocumentEmbedding: job gagal permanen', [
            'document_version_id' => $this->documentVersionId,
            'error'               => $exception->getMessage(),
        ]);

        DocumentChunk::where('document_version_id', $this->documentVersionId)
            ->where('embedding_status', 'pending')
            ->update(['embedding_status' => 'failed']);
    }

    private function setStatus(DocumentVersion $version, string $status): void
    {
        $version->update(['embedding_status' => $status]);
    }

    private function auditLog(Document $document, DocumentVersion $version, string $action, array $meta, string $level = 'info'): void
    {
        try {
            AiLog::create([
                'user_id' => $document->created_by,
                'action'  => $action,
                'meta'    => array_merge($meta, [
                    'document_id' => $document->id,
                    'version_id'  => $version->id,
                ]),
                'level'   => $level,
            ]);
        } catch (\Throwable) {}
    }
}