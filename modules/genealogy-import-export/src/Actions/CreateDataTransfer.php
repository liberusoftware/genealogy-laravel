<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class CreateDataTransfer
{
    public function execute(array $attributes): DataTransfer
    {
        return DataTransfer::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
