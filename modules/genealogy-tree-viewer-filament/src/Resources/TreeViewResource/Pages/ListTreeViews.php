<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class ListTreeViews extends ListRecords
{
    protected static string $resource = TreeViewResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
