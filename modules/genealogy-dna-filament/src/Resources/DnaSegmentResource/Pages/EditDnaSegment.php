<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Dna\Actions\DeleteDnaSegment;
use Liberu\Genealogy\Dna\Actions\UpdateDnaSegment;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource;

final class EditDnaSegment extends EditRecord
{
    protected static string $resource = DnaSegmentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDnaSegment::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteDnaSegment::class)->execute($record))];
    }
}
