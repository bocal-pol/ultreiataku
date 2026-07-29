<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\TripResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * RelationManager — Membres du Trip avec leurs rôles.
 * Affiché dans TripResource (ViewTrip + EditTrip).
 */
class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Membres';

    protected static ?string $pluralModelLabel = 'Membres';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('role')
                ->label('Rôle')
                ->options([
                    'organizer' => 'Organisateur',
                    'participant' => 'Participant',
                    'observer' => 'Observateur',
                ])
                ->required()
                ->default('participant'),

            Forms\Components\DateTimePicker::make('joined_at')
                ->label('Rejoint le')
                ->default(now()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_name')
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Pèlerin')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'organizer' => 'warning',
                        'participant' => 'success',
                        'observer' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'organizer' => 'Organisateur',
                        'participant' => 'Participant',
                        'observer' => 'Observateur',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('pivot.joined_at')
                    ->label('Rejoint le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'organizer' => 'Organisateur',
                        'participant' => 'Participant',
                        'observer' => 'Observateur',
                    ])
                    ->attribute('pivot.role'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options([
                                'participant' => 'Participant',
                                'observer' => 'Observateur',
                            ])
                            ->required()
                            ->default('participant'),
                        Forms\Components\DateTimePicker::make('joined_at')
                            ->label('Rejoint le')
                            ->default(now()),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
