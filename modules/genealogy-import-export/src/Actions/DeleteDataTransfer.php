<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Events\DataTransferDeleted;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class DeleteDataTransfer
{
    public function execute(DataTransfer $transfer): void
    {
        if ((string) $transfer->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The data transfer must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $transfer->delete());
        event(new DataTransferDeleted($transfer));
    }
}
