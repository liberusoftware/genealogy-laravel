<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaNote as CreateNote;
use Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource;

final class CreateDnaNote extends CreateRecord
{
    protected static string $resource = DnaNoteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateNote::class)->execute($data);
    }
}
