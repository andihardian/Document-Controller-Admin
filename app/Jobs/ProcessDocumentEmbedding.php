<?php

namespace App\Jobs;

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

    public function __construct(public readonly int $versionId) {}

    public function handle(DocumentParserService $parser, EmbeddingService $embedder): void
    {
        $version = DocumentVersion::findOrFail($this->versionId);

        // Update status
        $version->update(['embedding_status' => 'processing']);

        // 1. Parse PDF → teks
        $result = $parser->parse($version);

        if ($result['error']) {
            Log::error('ProcessDocumentEmbedding: parse gagal', [
                'version_id' => $this->versionId,
                'error'      => $result['error'],
            ]);
            $version->update(['embedding_status' => 'failed']);
            return;
        }

        // 2. Chunk + embed + simpan ke DB
        $saved = $embedder->processVersion($version, $result['text']);

        // 3. Update status selesai
        $version->update([
            'embedding_status' => $saved > 0 ? 'done' : 'failed',
            'page_count'       => $result['pages'],
        ]);

        Log::info('ProcessDocumentEmbedding: selesai', [
            'version_id'   => $this->versionId,
            'chunks_saved' => $saved,
            'pages'        => $result['pages'],
        ]);
    }

    public function failed(\Throwable $e): void
    {
        DocumentVersion::find($this->versionId)?->update(['embedding_status' => 'failed']);
        Log::error('ProcessDocumentEmbedding: job failed', ['error' => $e->getMessage()]);
    }
}