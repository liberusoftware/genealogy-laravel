<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource;

final class ListTrees extends ListRecords
{
    protected static string $resource = TreeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
