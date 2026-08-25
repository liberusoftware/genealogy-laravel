<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\ReviewCollaborationProposal;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages\CreateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages\EditCollaborationProposal;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages\ListCollaborationProposals;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;

final class CollaborationProposalResource extends Resource
{
    protected static ?string $model = CollaborationProposal::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('description')->columnSpanFull(),
            Select::make('status')->options([
                'proposed' => 'Proposed',
                'in_review' => 'In review',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'withdrawn' => 'Withdrawn',
            ])->disabled(),
            Textarea::make('metadata')->json()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('proposer_id')->label('Proposer')->sortable(),
            TextColumn::make('reviewed_at')->dateTime()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            Action::make('review')
                ->label('Review')
                ->visible(fn (CollaborationProposal $record): bool => in_array($record->status, ['proposed', 'in_review'], true))
                ->form([
                    Select::make('status')->options(['in_review' => 'In review', 'approved' => 'Approved', 'rejected' => 'Rejected'])->required(),
                ])
                ->action(fn (Model $record, array $data): CollaborationProposal => app(ReviewCollaborationProposal::class)->execute($record, $data['status'])),
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListCollaborationProposals::route('/'),
            'create' => CreateCollaborationProposal::route('/create'),
            'edit' => EditCollaborationProposal::route('/{record}/edit'),
        ];
    }
}
