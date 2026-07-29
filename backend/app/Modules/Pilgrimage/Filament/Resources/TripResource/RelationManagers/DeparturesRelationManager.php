<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\TripResource\RelationManagers;

use App\Modules\Pilgrimage\Enums\DepartureStatus;
use App\Modules\Pilgrimage\Filament\Resources\DepartureResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * RelationManager — Départs du Trip.
 * Affiché dans TripResource (ViewTrip + EditTrip).
 */
class DeparturesRelationManager extends RelationManager
{
    protected static string $relationship = 'departures';

    protected static ?string $title = 'Départs';

    protected static ?string $pluralModelLabel = 'Départs';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('pilgrim_id')
                ->label('Pèlerin')
                ->relationship('pilgrim', 'display_name')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('start_stage_id')
                ->label('Étape de départ')
                ->relationship('startStage', 'code')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('end_stage_id')
                ->label('Étape d\'arrivée')
                ->relationship('endStage', 'code')
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('planned_start_date')
                ->label('Départ planifié')
                ->required(),

            Forms\Components\DatePicker::make('planned_end_date')
                ->label('Arrivée planifiée'),

            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'planned' => 'Planifié',
                    'active' => 'En cours',
                    'paused' => 'En pause',
                    'completed' => 'Terminé',
                    'abandoned' => 'Abandonné',
                ])
                ->required()
                ->default('planned'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('pilgrim.display_name')
                    ->label('Pèlerin')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('startStage.code')
                    ->label('De')
                    ->badge(),

                Tables\Columns\TextColumn::make('endStage.code')
                    ->label('À')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (DepartureStatus $state): string => match ($state) {
                        DepartureStatus::Planned => 'info',
                        DepartureStatus::Active => 'success',
                        DepartureStatus::Paused => 'warning',
                        DepartureStatus::Completed => 'gray',
                        DepartureStatus::Abandoned => 'danger',
                    }),

                Tables\Columns\TextColumn::make('planned_start_date')
                    ->label('Départ planifié')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'planned' => 'Planifié',
                        'active' => 'En cours',
                        'paused' => 'En pause',
                        'completed' => 'Terminé',
                        'abandoned' => 'Abandonné',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => DepartureResource::canCreate()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('planned_start_date');
    }
}
