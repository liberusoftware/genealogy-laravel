<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class EditTreeView extends EditRecord
{
    protected static string $resource = TreeViewResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
