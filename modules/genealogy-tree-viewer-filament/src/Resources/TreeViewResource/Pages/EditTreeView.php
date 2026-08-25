<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\TreeViewer\Actions\DeleteTreeView;
use Liberu\Genealogy\TreeViewer\Actions\UpdateTreeView;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class EditTreeView extends EditRecord
{
    protected static string $resource = TreeViewResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTreeView::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteTreeView::class)->execute($record))];
    }
}
