<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages\CreateCarePlan;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages\EditCarePlan;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages\ListCarePlans;
use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;

final class CarePlanResource extends Resource
{
    protected static ?string $model = CarePlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant !== null, 403);

        return parent::getEloquentQuery()->forTenant($tenant->getKey());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('status')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'completed' => 'Completed',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListCarePlans::route('/'),
            'create' => CreateCarePlan::route('/create'),
            'edit' => EditCarePlan::route('/{record}/edit'),
        ];
    }
}
