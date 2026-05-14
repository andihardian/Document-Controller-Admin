<?php

return [

    // ─── Groq (Chat Model) ────────────────────────────────────────────────────
    'groq_api_key'    => env('GROQ_API_KEY', ''),
    'chat_model'      => env('AI_CHAT_MODEL', 'llama-3.1-8b-instant'),

    // ─── OpenAI (Embedding) ───────────────────────────────────────────────────
    // Kosongkan jika tidak punya credit → sistem pakai simple keyword embedding
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    // ─── Token & Context ──────────────────────────────────────────────────────
    'max_tokens'           => (int) env('AI_MAX_TOKENS', 1500),
    'max_context_tokens'   => (int) env('AI_MAX_CONTEXT_TOKENS', 8000),

    // ─── RAG Settings ─────────────────────────────────────────────────────────
    'chunk_size'           => (int) env('AI_CHUNK_SIZE', 500),
    'chunk_overlap'        => (int) env('AI_CHUNK_OVERLAP', 50),
    'max_chunks'           => (int) env('AI_MAX_CHUNKS', 5),

    // Threshold similarity (0.0 - 1.0)
    // Pakai 0.1 jika menggunakan simple keyword embedding (lebih longgar)
    // Pakai 0.3 jika menggunakan OpenAI embedding
    'similarity_threshold' => (float) env('AI_SIMILARITY_THRESHOLD', 0.1),

    // ─── Rate Limit ───────────────────────────────────────────────────────────
    'rate_limit'      => (int) env('AI_RATE_LIMIT', 30),

    // ─── OCR ──────────────────────────────────────────────────────────────────
    'ocr_enabled'     => env('AI_OCR_ENABLED', false),
    'ocr_language'    => env('AI_OCR_LANGUAGE', 'ind+eng'),

    // ─── Queue ────────────────────────────────────────────────────────────────
    'queue'           => env('AI_QUEUE', 'default'),

];