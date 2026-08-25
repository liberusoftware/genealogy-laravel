<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers;

final class LifeEventsRelationManager extends PersonSupportingRelationManager
{
    protected static string $relationship = 'lifeEvents';
}
