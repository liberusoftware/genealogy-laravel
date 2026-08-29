<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Events;

use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class EvidenceRecordReviewed
{
    public bool $afterCommit = true;

    public function __construct(public EvidenceRecord $record) {}
}
