<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaGroup;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaGroup
{
    public function execute(DnaGroup $group, array $attributes): DnaGroup
    {
        $this->assertTeam($group);
        $values = Arr::only($attributes, ['name', 'description', 'status', 'metadata']);
        if (array_key_exists('name', $values) && trim((string) $values['name']) === '') {
            throw new InvalidArgumentException('A DNA group name is required.');
        }
        DB::transaction(fn (): bool => $group->update($values));

        return $group->refresh();
    }

    private function assertTeam(DnaGroup $group): void
    {
        if ((string) $group->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA group must belong to the active team.');
        }
    }
}
