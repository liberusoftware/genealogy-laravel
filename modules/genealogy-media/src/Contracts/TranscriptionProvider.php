<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Contracts;

/** Optional OCR/handwriting integration; hosts bind an implementation explicitly. */
interface TranscriptionProvider
{
    public function isAvailable(): bool;

    /** @return array{text: string, confidence: ?float, language: ?string, metadata: array<string, mixed>} */
    public function transcribe(string $absolutePath): array;
}
