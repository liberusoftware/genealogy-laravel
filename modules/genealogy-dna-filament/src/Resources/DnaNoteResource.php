<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Dna\Actions\DeleteDnaNote;
use Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource\Pages\CreateDnaNote as CreateDnaNotePage;
use Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource\Pages\ListDnaNotes;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\Dna\Models\DnaNote;

final class DnaNoteResource extends Resource
{
    protected static ?string $model = DnaNote::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'DNA & Matching';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('noteable_type')->options([DnaKit::class => 'Kit', DnaMatch::class => 'Match'])->required(),
            TextInput::make('noteable_id')->uuid()->required(),
            Textarea::make('body')->required()->maxLength(50000)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('noteable_type')->label('Target')->sortable(),
            TextColumn::make('noteable_id')->label('Target ID')->sortable(),
            TextColumn::make('body')->limit(100)->searchable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([DeleteAction::make()->action(fn (DnaNote $record): mixed => app(DeleteDnaNote::class)->execute($record))]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListDnaNotes::route('/'), 'create' => CreateDnaNotePage::route('/create')];
    }
}
