<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class CreateTreeView extends CreateRecord
{
    protected static string $resource = TreeViewResource::class;
}
