<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource;

final class ListCollaborationMemberships extends ListRecords
{
    protected static string $resource = CollaborationMembershipResource::class;
}
