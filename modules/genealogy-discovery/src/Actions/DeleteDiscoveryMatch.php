<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchDeleted;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDiscoveryMatch
{
    public function execute(DiscoveryMatch $match): void
    {
        $schema = $match->getConnection()->getSchemaBuilder();
        if ($schema->hasColumn($match->getTable(), 'team_id') && (string) $match->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The discovery match must belong to the active team.');
        }

        DB::transaction(fn (): mixed => $match->delete());
        event(new DiscoveryMatchDeleted($match));
    }
}
