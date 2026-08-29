<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\ImportExport\Events\DataTransferCreated;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class CreateDataTransfer
{
    public function execute(array $attributes): DataTransfer
    {
        $values = Arr::only($attributes, ['name', 'format', 'direction', 'records_count', 'status', 'metadata']);
        $this->validate($values);

        $transfer = DataTransfer::query()->getConnection()->transaction(function () use ($values): DataTransfer {
            $transfer = DataTransfer::query()->create($values);

            return $transfer;
        });

        if (app()->bound('events')) {
            event(new DataTransferCreated($transfer));
        }

        return $transfer;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw new InvalidArgumentException('A data transfer name is required.');
        }
        if (isset($values['format']) && ! in_array($values['format'], DataTransfer::FORMATS, true)) {
            throw new InvalidArgumentException('The transfer format is not supported.');
        }
        if (isset($values['direction']) && ! in_array($values['direction'], DataTransfer::DIRECTIONS, true)) {
            throw new InvalidArgumentException('The transfer direction is not supported.');
        }
        if (isset($values['status']) && ! in_array($values['status'], DataTransfer::STATUSES, true)) {
            throw new InvalidArgumentException('The transfer status is not supported.');
        }
    }
}
