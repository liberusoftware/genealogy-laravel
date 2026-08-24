<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class CreateGenealogyReport
{
    public function execute(array $attributes): GenealogyReport
    {
        return GenealogyReport::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
