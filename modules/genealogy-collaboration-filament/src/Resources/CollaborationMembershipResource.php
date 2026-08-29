<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource\Pages\EditCollaborationMembership;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource\Pages\ListCollaborationMemberships;
use Liberu\Genealogy\Collaboration\Models\CollaborationMembership;

final class CollaborationMembershipResource extends Resource
{
    protected static ?string $model = CollaborationMembership::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('user_id')->numeric()->disabled(),
            TextInput::make('space_id')->uuid()->disabled(),
            Select::make('role')->options(array_combine(CollaborationMembership::ROLES, CollaborationMembership::ROLES))->required(),
            Select::make('status')->options(['active' => 'Active', 'suspended' => 'Suspended'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('user_id')->label('User')->sortable(), TextColumn::make('role')->badge()->sortable(), TextColumn::make('status')->badge()->sortable(), TextColumn::make('joined_at')->dateTime()->sortable()]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['index' => ListCollaborationMemberships::route('/'), 'edit' => EditCollaborationMembership::route('/{record}/edit')];
    }
}
