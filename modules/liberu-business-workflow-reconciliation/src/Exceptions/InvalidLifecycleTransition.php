<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Exceptions;

use DomainException;

final class InvalidLifecycleTransition extends DomainException
{
    public static function between(string $from, string $to): self
    {
        return new self("Cannot transition reconciliation case from [{$from}] to [{$to}].");
    }
}
