<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages\CreateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages\EditCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages\ListCollaborationDiscussions;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;

final class CollaborationDiscussionResource extends Resource
{
    protected static ?string $model = CollaborationDiscussion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')->required()->maxLength(50000)->columnSpanFull(),
            Select::make('status')->options(['open' => 'Open', 'resolved' => 'Resolved', 'archived' => 'Archived'])->required(),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('body')->limit(80)->searchable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('author_id')->label('Author')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteCollaborationDiscussion::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListCollaborationDiscussions::route('/'), 'create' => CreateCollaborationDiscussion::route('/create'), 'edit' => EditCollaborationDiscussion::route('/{record}/edit')];
    }
}
