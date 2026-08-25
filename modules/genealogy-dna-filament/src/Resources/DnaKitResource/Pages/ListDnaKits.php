<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Dna\Actions\ImportDnaKit;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

final class ListDnaKits extends ListRecords
{
    protected static string $resource = DnaKitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import')
                ->label('Import DNA kit')
                ->form([
                    TextInput::make('name')->required()->maxLength(255),
                    FileUpload::make('file')->storeFiles(false)->required(),
                    Select::make('consent_status')->options(['pending' => 'Pending', 'granted' => 'Granted', 'revoked' => 'Revoked'])->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'];
                    $path = is_string($file) ? $file : $file->getRealPath();
                    $content = file_get_contents($path);
                    if ($content === false) {
                        throw new \RuntimeException('The uploaded DNA file could not be read.');
                    }
                    app(ImportDnaKit::class)->execute($content, [
                        'name' => $data['name'],
                        'consent_status' => $data['consent_status'],
                    ]);
                }),
        ];
    }
}
