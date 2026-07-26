<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Enums\DepartureStatus;
use App\Modules\Pilgrimage\Filament\Resources\DepartureResource\Pages;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ULTREIA-34 — DepartureResource Filament.
 * Liste tous les Departures, filtres par Trip et Statut, édition.
 * bug_rule_004 : CreateAction avec ->visible(fn () => DepartureResource::canCreate()).
 */
class DepartureResource extends Resource
{
    protected static ?string $model = Departure::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Départ';

    protected static ?string $pluralModelLabel = 'Départs';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Voyage')->schema([
                Forms\Components\Select::make('trip_id')
                    ->label('Trip')
                    ->relationship('trip', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('pilgrim_id')
                    ->label('Pèlerin')
                    ->relationship('pilgrim', 'display_name')
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Étapes')->schema([
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
            ])->columns(2),

            Forms\Components\Section::make('Dates')->schema([
                Forms\Components\DatePicker::make('planned_start_date')
                    ->label('Départ planifié')
                    ->required(),

                Forms\Components\DatePicker::make('planned_end_date')
                    ->label('Arrivée planifiée'),

                Forms\Components\DatePicker::make('actual_start_date')
                    ->label('Départ réel'),
            ])->columns(3),

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

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->maxLength(2000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trip.name')
                    ->label('Trip')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pilgrim.display_name')
                    ->label('Pèlerin')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('startStage.code')
                    ->label('Départ')
                    ->badge(),

                Tables\Columns\TextColumn::make('endStage.code')
                    ->label('Arrivée')
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

                Tables\Columns\TextColumn::make('actual_start_date')
                    ->label('Départ réel')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trip_id')
                    ->label('Trip')
                    ->relationship('trip', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'planned' => 'Planifié',
                        'active' => 'En cours',
                        'paused' => 'En pause',
                        'completed' => 'Terminé',
                        'abandoned' => 'Abandonné',
                    ]),

                Tables\Filters\SelectFilter::make('pilgrim_id')
                    ->label('Pèlerin')
                    ->relationship('pilgrim', 'display_name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('planned_start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartures::route('/'),
            'create' => Pages\CreateDeparture::route('/create'),
            'view' => Pages\ViewDeparture::route('/{record}'),
            'edit' => Pages\EditDeparture::route('/{record}/edit'),
        ];
    }
}
