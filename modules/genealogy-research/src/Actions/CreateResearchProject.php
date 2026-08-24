<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class CreateResearchProject
{
    public function execute(array $attributes): ResearchProject
    {
        return ResearchProject::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
