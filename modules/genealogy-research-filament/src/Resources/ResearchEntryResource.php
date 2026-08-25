<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Research\Actions\DeleteResearchEntry;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages\CreateResearchEntry;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages\EditResearchEntry;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages\ListResearchEntries;
use Liberu\Genealogy\Research\Models\ResearchEntry;

final class ResearchEntryResource extends Resource
{
    protected static ?string $model = ResearchEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('research_project_id')->required()->uuid(),
            Select::make('kind')->options(array_combine(ResearchEntry::KINDS, ResearchEntry::KINDS))->required(),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('body')->maxLength(50000)->columnSpanFull(),
            Select::make('status')->options(['open' => 'Open', 'in_progress' => 'In progress', 'completed' => 'Completed'])->required(),
            DatePicker::make('due_date'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('kind')->badge()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('due_date')->date()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteResearchEntry::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListResearchEntries::route('/'),
            'create' => CreateResearchEntry::route('/create'),
            'edit' => EditResearchEntry::route('/{record}/edit'),
        ];
    }
}
