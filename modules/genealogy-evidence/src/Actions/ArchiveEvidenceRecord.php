<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordArchived;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class ArchiveEvidenceRecord
{
    public function execute(EvidenceRecord $record): EvidenceRecord
    {
        if ($record->status === 'archived') {
            throw new InvalidArgumentException('The evidence record is already archived.');
        }

        DB::transaction(function () use ($record): void {
            $record->forceFill(['status' => 'archived'])->save();
        });
        event(new EvidenceRecordArchived($record->refresh()));

        return $record;
    }
}
