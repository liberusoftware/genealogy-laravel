<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Data;

use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final readonly class ExportedGenealogyData
{
    public function __construct(
        public DataTransfer $transfer,
        public string $content,
        public string $format,
        public string $filename,
        public string $contentType,
    ) {}
}
