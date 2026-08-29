<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchReviewed;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ReviewDiscoveryMatch
{
    public function execute(DiscoveryMatch $match, string $status): DiscoveryMatch
    {
        $schema = $match->getConnection()->getSchemaBuilder();
        if ($schema->hasColumn($match->getTable(), 'team_id') && (string) $match->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The discovery match must belong to the active team.');
        }
        if (! in_array($status, ['active', 'completed', 'dismissed'], true)) {
            throw new InvalidArgumentException('The discovery review status is invalid.');
        }

        DB::transaction(fn (): bool => $match->update(['status' => $status, 'reviewed_at' => now()]));
        $match = $match->refresh();
        event(new DiscoveryMatchReviewed($match));

        return $match;
    }
}
