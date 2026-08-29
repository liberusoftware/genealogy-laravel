<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Reports\Actions\DeleteGenealogyReport;
use Liberu\Genealogy\Reports\Actions\UpdateGenealogyReport;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class EditGenealogyReport extends EditRecord
{
    protected static string $resource = GenealogyReportResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateGenealogyReport::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteGenealogyReport::class)->execute($record))];
    }
}
