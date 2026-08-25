<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordReviewed;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class ReviewEvidenceRecord
{
    public function execute(EvidenceRecord $record): EvidenceRecord
    {
        if ($record->status === 'archived') {
            throw new InvalidArgumentException('An archived evidence record cannot be reviewed.');
        }

        if ($record->assertion === null && $record->proof_conclusion !== null) {
            throw new InvalidArgumentException('A proof conclusion requires an assertion.');
        }

        DB::transaction(function () use ($record): void {
            $record->forceFill([
                'status' => 'completed',
                'reviewed_at' => now(),
            ])->save();
        });
        event(new EvidenceRecordReviewed($record->refresh()));

        return $record;
    }
}
