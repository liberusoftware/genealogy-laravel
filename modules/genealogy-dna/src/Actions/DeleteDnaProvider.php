<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaProvider;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaProvider
{
    public function execute(DnaProvider $provider): void
    {
        if ((string) $provider->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA provider must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $provider->delete());
    }
}
