<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Events;

use Liberu\Genealogy\ImportExport\Models\DataTransfer;

final class DataTransferUndone
{
    public function __construct(public DataTransfer $transfer) {}
}
