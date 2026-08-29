<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Actions\CreateTree as CreateTreeAction;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource;

final class CreateTree extends CreateRecord
{
    protected static string $resource = TreeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateTreeAction::class)->execute($data);
    }
}
