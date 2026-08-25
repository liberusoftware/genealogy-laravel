<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;

final class CreateCollaborationSpace
{
    public function execute(array $attributes): CollaborationSpace
    {
        return CollaborationSpace::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
