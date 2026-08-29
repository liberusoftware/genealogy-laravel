<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;

final class CreateCollaborationSpace
{
    public function execute(array $attributes): CollaborationSpace
    {
        $values = Arr::only($attributes, ['name', 'status', 'metadata']);
        $values['name'] = trim((string) ($values['name'] ?? ''));
        if ($values['name'] === '') {
            throw new InvalidArgumentException('A collaboration space name is required.');
        }
        if (isset($values['status']) && ! in_array($values['status'], CollaborationSpace::STATUSES, true)) {
            throw new InvalidArgumentException('The collaboration space status is invalid.');
        }

        return CollaborationSpace::query()->create($values);
    }
}
