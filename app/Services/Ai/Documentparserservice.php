<?php

namespace App\Services\AI;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentParserService
{
    /**
     * Parse teks dari DocumentVersion (PDF / DOCX / TXT).
     * Return: ['text' => string, 'pages' => int, 'error' => string|null]
     */
    public function parse(DocumentVersion $version): array
    {
        $path = Storage::disk('public')->path($version->file_path);

        if (! file_exists($path)) {
            return $this->fail("File tidak ditemukan: {$version->file_path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf'        => $this->parsePdf($path),
            'docx', 'doc' => $this->parseDocx($path),
            'txt'        => $this->parseTxt($path),
            default      => $this->fail("Format '{$extension}' tidak didukung."),
        };
    }

    // ─── PDF ─────────────────────────────────────────────────────────────────

    private function parsePdf(string $path): array
    {
        try {
            $parser   = new PdfParser();
            $pdf      = $parser->parseFile($path);
            $pages    = $pdf->getPages();
            $pageCount = count($pages);
            $texts    = [];

            foreach ($pages as $i => $page) {
                $pageText = $page->getText();
                if (trim($pageText) !== '') {
                    // Tandai halaman agar chunker bisa menyimpan info halaman
                    $texts[] = "[[PAGE:" . ($i + 1) . "]]\n" . $this->cleanText($pageText);
                }
            }

            $fullText = implode("\n\n", $texts);

            if (empty(trim($fullText))) {
                return $this->fail('PDF tidak mengandung teks yang dapat dibaca (mungkin scan). Pertimbangkan OCR.');
            }

            return ['text' => $fullText, 'pages' => $pageCount, 'error' => null];

        } catch (\Throwable $e) {
            Log::error('DocumentParserService::parsePdf', ['error' => $e->getMessage(), 'path' => $path]);
            return $this->fail('Gagal parse PDF: ' . $e->getMessage());
        }
    }

    // ─── DOCX ────────────────────────────────────────────────────────────────

    private function parseDocx(string $path): array
    {
        try {
            $phpWord  = IOFactory::load($path);
            $sections = $phpWord->getSections();
            $texts    = [];
            $page     = 1;

            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {
                    $text = $this->extractWordElement($element);
                    if ($text !== '') {
                        $texts[] = $text;
                    }

                    // Deteksi page break sederhana
                    if (method_exists($element, 'getPageBreak') && $element->getPageBreak()) {
                        $texts[] = "[[PAGE:" . (++$page) . "]]";
                    }
                }
            }

            $fullText = implode("\n", $texts);

            return ['text' => $this->cleanText($fullText), 'pages' => $page, 'error' => null];

        } catch (\Throwable $e) {
            Log::error('DocumentParserService::parseDocx', ['error' => $e->getMessage(), 'path' => $path]);
            return $this->fail('Gagal parse DOCX: ' . $e->getMessage());
        }
    }

    // ─── TXT ─────────────────────────────────────────────────────────────────

    private function parseTxt(string $path): array
    {
        try {
            $content = file_get_contents($path);

            if ($content === false) {
                return $this->fail('Gagal membaca file TXT.');
            }

            return ['text' => $this->cleanText($content), 'pages' => 1, 'error' => null];

        } catch (\Throwable $e) {
            return $this->fail('Gagal parse TXT: ' . $e->getMessage());
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Rekursif extract teks dari element PhpWord.
     */
    private function extractWordElement(mixed $element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $raw = $element->getText();
            $text .= is_string($raw) ? $raw : '';
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= ' ' . $this->extractWordElement($child);
            }
        }

        return trim($text);
    }

    /**
     * Bersihkan whitespace berlebih dan karakter tidak perlu.
     */
    private function cleanText(string $text): string
    {
        // Hapus null bytes
        $text = str_replace("\0", '', $text);
        // Normalkan line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Hapus baris kosong berlebih (lebih dari 2 baris kosong → 2)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        // Hapus spasi di awal/akhir setiap baris
        $lines = array_map('trim', explode("\n", $text));
        $text  = implode("\n", $lines);

        return trim($text);
    }

    private function fail(string $message): array
    {
        return ['text' => '', 'pages' => 0, 'error' => $message];
    }
}