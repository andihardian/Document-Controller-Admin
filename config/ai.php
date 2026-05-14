<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Chat Model
    |--------------------------------------------------------------------------
    | Model LLM untuk chat. Ganti ke 'gpt-4o' untuk hasil lebih baik.
    */
    'chat_model' => env('AI_CHAT_MODEL', 'gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | Embedding Model
    |--------------------------------------------------------------------------
    */
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    /*
    |--------------------------------------------------------------------------
    | Token Limits
    |--------------------------------------------------------------------------
    */
    'max_tokens'     => (int) env('AI_MAX_TOKENS', 1500),
    'max_context_tokens' => (int) env('AI_MAX_CONTEXT_TOKENS', 8000),

    /*
    |--------------------------------------------------------------------------
    | RAG Settings
    |--------------------------------------------------------------------------
    */
    'max_chunks'           => (int) env('AI_MAX_CHUNKS', 5),
    'similarity_threshold' => (float) env('AI_SIMILARITY_THRESHOLD', 0.3),
    'chunk_size'           => (int) env('AI_CHUNK_SIZE', 500),       // kata per chunk
    'chunk_overlap'        => (int) env('AI_CHUNK_OVERLAP', 50),     // overlap antar chunk

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Maksimum pesan per menit per user.
    */
    'rate_limit' => (int) env('AI_RATE_LIMIT', 30),

    /*
    |--------------------------------------------------------------------------
    | OCR
    |--------------------------------------------------------------------------
    */
    'ocr_enabled' => env('AI_OCR_ENABLED', false),
    'ocr_language' => env('AI_OCR_LANGUAGE', 'ind+eng'),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */
    'queue' => env('AI_QUEUE', 'default'),

];