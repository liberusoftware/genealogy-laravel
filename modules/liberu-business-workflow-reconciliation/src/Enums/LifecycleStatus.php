<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Enums;

enum LifecycleStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::Archived],
            self::Active => [self::Completed, self::Archived],
            self::Completed, self::Archived => [],
        };
    }
}
