<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Contracts;

/**
 * Optional integration boundary for face detection providers.
 * Implementations must be installed and explicitly bound by the host app.
 */
interface FaceRecognitionProvider
{
    public function isAvailable(): bool;

    /** @return list<array{bounding_box: array<string, float>, confidence: ?float, face_id: ?string}> */
    public function detect(string $absolutePath): array;
}
