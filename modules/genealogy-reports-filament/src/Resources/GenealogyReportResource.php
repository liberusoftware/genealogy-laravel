<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Reports\Actions\DeleteGenealogyReport;
use Liberu\Genealogy\Reports\Actions\GenerateGenealogyReport;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages\CreateGenealogyReport;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages\EditGenealogyReport;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource\Pages\ListGenealogyReports;
use Liberu\Genealogy\Reports\Models\GenealogyReport;

final class GenealogyReportResource extends Resource
{
    protected static ?string $model = GenealogyReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('type')->options(array_combine(GenealogyReport::TYPES, array_map(static fn (string $type): string => ucwords(str_replace('_', ' ', $type)), GenealogyReport::TYPES)))->required(),
            Select::make('status')->options(array_combine(GenealogyReport::STATUSES, array_map(static fn (string $status): string => ucfirst($status), GenealogyReport::STATUSES)))->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            Action::make('generate')->requiresConfirmation()->action(fn (GenealogyReport $record): GenealogyReport => app(GenerateGenealogyReport::class)->execute($record)),
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteGenealogyReport::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListGenealogyReports::route('/'),
            'create' => CreateGenealogyReport::route('/create'),
            'edit' => EditGenealogyReport::route('/{record}/edit'),
        ];
    }
}
