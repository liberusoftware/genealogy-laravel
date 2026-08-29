<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateCollaborationSpace
{
    public function execute(CollaborationSpace $space, array $attributes): CollaborationSpace
    {
        $this->assertTeam($space);
        $values = Arr::only($attributes, ['name', 'status', 'metadata']);
        if (array_key_exists('name', $values) && trim((string) $values['name']) === '') {
            throw new InvalidArgumentException('A collaboration space name is required.');
        }
        DB::transaction(fn (): bool => $space->update($values));

        return $space->refresh();
    }

    private function assertTeam(CollaborationSpace $space): void
    {
        if ((string) $space->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The collaboration space must belong to the active team.');
        }
    }
}
