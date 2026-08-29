<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Events\DataTransferUpdated;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class UpdateDataTransfer
{
    /** @param array<string, mixed> $attributes */
    public function execute(DataTransfer $transfer, array $attributes): DataTransfer
    {
        if ((string) $transfer->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The data transfer must belong to the active team.');
        }

        $values = Arr::only($attributes, ['name', 'format', 'direction', 'records_count', 'status', 'metadata']);
        (new CreateDataTransfer())->validate(array_merge($transfer->toArray(), $values));
        $transfer->getConnection()->transaction(function () use ($transfer, $values): void {
            $transfer->update($values);
        });

        $transfer = $transfer->refresh();
        if (app()->bound('events')) {
            event(new DataTransferUpdated($transfer));
        }

        return $transfer;
    }
}
