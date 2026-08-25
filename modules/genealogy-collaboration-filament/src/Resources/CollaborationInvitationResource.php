<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Collaboration\Actions\RevokeCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource\Pages\CreateCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource\Pages\ListCollaborationInvitations;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;

final class CollaborationInvitationResource extends Resource
{
    protected static ?string $model = CollaborationInvitation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->email()->required()->maxLength(255),
            TextInput::make('space_id')->uuid()->nullable(),
            Select::make('role')->options(array_combine(CollaborationInvitation::ROLES, CollaborationInvitation::ROLES))->required(),
            Select::make('status')->options(array_combine(CollaborationInvitation::STATUSES, CollaborationInvitation::STATUSES))->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('email')->searchable()->sortable(),
            TextColumn::make('role')->badge()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('expires_at')->dateTime()->sortable(),
        ])->recordActions([
            Action::make('revoke')->visible(fn (CollaborationInvitation $record): bool => $record->status === 'pending')->requiresConfirmation()->action(fn (CollaborationInvitation $record): CollaborationInvitation => app(RevokeCollaborationInvitation::class)->execute($record)),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListCollaborationInvitations::route('/'), 'create' => CreateCollaborationInvitation::route('/create')];
    }
}
