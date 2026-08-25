<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Actions\DeleteTree;
use Liberu\Genealogy\GenealogyCore\Actions\UpdateTree;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource;

final class EditTree extends EditRecord
{
    protected static string $resource = TreeResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTree::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteTree::class)->execute($record))];
    }
}
