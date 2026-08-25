<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView as CreateTreeViewAction;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class CreateTreeView extends CreateRecord
{
    protected static string $resource = TreeViewResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateTreeViewAction::class)->execute($data);
    }
}
