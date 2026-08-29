<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Services\DnaFileVault;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaKit
{
    public function __construct(private readonly DnaFileVault $vault) {}

    public function execute(DnaKit $kit): void
    {
        if ((string) $kit->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA kit must belong to the active team.');
        }

        DB::transaction(function () use ($kit): void {
            if ($kit->file_path !== null) {
                $this->vault->delete($kit->file_path);
            }
            $kit->delete();
        });
    }
}
