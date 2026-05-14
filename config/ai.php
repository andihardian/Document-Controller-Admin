<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    | Chat menggunakan Groq (gratis), Embedding menggunakan OpenAI.
    */

    // ── Chat Model (Groq) ─────────────────────────────────────────────────
    // Model gratis Groq: llama-3.1-8b-instant, llama-3.3-70b-versatile,
    //                    mixtral-8x7b-32768, gemma2-9b-it
    'chat_model' => env('AI_CHAT_MODEL', 'llama-3.1-8b-instant'),

    // ── Groq API Key ──────────────────────────────────────────────────────
    'groq_api_key' => env('GROQ_API_KEY', ''),

    // ── Embedding Model (OpenAI) ──────────────────────────────────────────
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    // ── Token Limits ─────────────────────────────────────────────────────
    'max_tokens'         => (int) env('AI_MAX_TOKENS', 1500),
    'max_context_tokens' => (int) env('AI_MAX_CONTEXT_TOKENS', 8000),

    // ── RAG Settings ─────────────────────────────────────────────────────
    'max_chunks'           => (int)   env('AI_MAX_CHUNKS', 5),
    'similarity_threshold' => (float) env('AI_SIMILARITY_THRESHOLD', 0.3),
    'chunk_size'           => (int)   env('AI_CHUNK_SIZE', 500),
    'chunk_overlap'        => (int)   env('AI_CHUNK_OVERLAP', 50),

    // ── Rate Limiting ─────────────────────────────────────────────────────
    'rate_limit' => (int) env('AI_RATE_LIMIT', 30),

    // ── OCR ──────────────────────────────────────────────────────────────
    'ocr_enabled'  => env('AI_OCR_ENABLED', false),
    'ocr_language' => env('AI_OCR_LANGUAGE', 'ind+eng'),

    // ── Queue ─────────────────────────────────────────────────────────────
    'queue' => env('AI_QUEUE', 'default'),

];