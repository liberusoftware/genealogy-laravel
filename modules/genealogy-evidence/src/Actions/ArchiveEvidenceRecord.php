<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordArchived;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ArchiveEvidenceRecord
{
    public function execute(EvidenceRecord $record): EvidenceRecord
    {
        $this->ensureActiveTeam($record);

        if ($record->status === 'archived') {
            throw new InvalidArgumentException('The evidence record is already archived.');
        }

        DB::transaction(function () use ($record): void {
            $record->forceFill(['status' => 'archived'])->save();
        });
        event(new EvidenceRecordArchived($record->refresh()));

        return $record;
    }

    private function ensureActiveTeam(EvidenceRecord $record): void
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The evidence record must belong to the active team.');
        }
    }
}
