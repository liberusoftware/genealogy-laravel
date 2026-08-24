<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

final class CreateEvidenceRecord
{
    public function execute(array $attributes): EvidenceRecord
    {
        return EvidenceRecord::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
