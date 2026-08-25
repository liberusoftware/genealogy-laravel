<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\CreateDnaSegment as CreateSegment;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource;

final class CreateDnaSegment extends CreateRecord
{
    protected static string $resource = DnaSegmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateSegment::class)->execute($data);
    }
}
