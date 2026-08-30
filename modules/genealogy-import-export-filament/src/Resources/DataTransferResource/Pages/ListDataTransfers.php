<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\ImportExport\Actions\ExportGenealogyData;
use Liberu\Genealogy\ImportExport\Filament\Resources\DataTransferResource;

final class ListDataTransfers extends ListRecords
{
    protected static string $resource = DataTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('export')
                ->label('Export genealogy')
                ->form([
                    TextInput::make('name')->default('Genealogy export')->required()->maxLength(255),
                    Select::make('format')->options(['gedcom' => 'GEDCOM 5.5.1', 'gedcom-7' => 'GEDCOM 7.0', 'gedcom-x' => 'GEDCOM X JSON', 'gramps-xml' => 'GRAMPS XML'])->default('gedcom')->required(),
                ])
                ->action(function (array $data): mixed {
                    $result = app(ExportGenealogyData::class)->execute($data['format'], $data['name']);

                    return response()->streamDownload(static function () use ($result): void {
                        echo $result->content;
                    }, $result->filename, ['Content-Type' => $result->contentType]);
                }),
        ];
    }
}
