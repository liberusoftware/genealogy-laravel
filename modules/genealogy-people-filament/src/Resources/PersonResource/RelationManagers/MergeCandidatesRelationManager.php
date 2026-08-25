<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament\Resources\PersonResource\RelationManagers;

final class MergeCandidatesRelationManager extends PersonSupportingRelationManager
{
    protected static string $relationship = 'mergeCandidates';
}
