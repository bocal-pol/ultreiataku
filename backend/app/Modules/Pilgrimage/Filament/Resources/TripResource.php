<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\TripStatus;
use App\Modules\Pilgrimage\Filament\Resources\TripResource\Pages;
use App\Modules\Pilgrimage\Filament\Resources\TripResource\RelationManagers\DeparturesRelationManager;
use App\Modules\Pilgrimage\Filament\Resources\TripResource\RelationManagers\MembersRelationManager;
use App\Modules\Pilgrimage\Models\Trip;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ULTREIA-34 — TripResource Filament (lecture + modération).
 *
 * bug_rule_004 : CreateAction explicite avec ->visible() appliqué
 * pour respecter canCreate() de la Policy.
 */
class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Trip';

    protected static ?string $pluralModelLabel = 'Trips';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')
                ->label('Nom du Trip')
                ->required()
                ->maxLength(200),

            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'planned' => 'Planifié',
                    'active' => 'Actif',
                    'completed' => 'Terminé',
                    'cancelled' => 'Annulé',
                ])
                ->required(),

            Forms\Components\Select::make('configuration')
                ->label('Configuration')
                ->options([
                    'solo' => 'Solo',
                    'duo' => 'Duo',
                    'group' => 'Groupe',
                ]),

            Forms\Components\Toggle::make('is_public')
                ->label('Public'),

            Forms\Components\DatePicker::make('estimated_start_date')
                ->label('Départ estimé'),

            Forms\Components\DatePicker::make('estimated_end_date')
                ->label('Arrivée estimée'),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->maxLength(5000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('organizer.display_name')
                    ->label('Organisateur')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (TripStatus $state): string => match ($state) {
                        TripStatus::Planned => 'info',
                        TripStatus::Active => 'success',
                        TripStatus::Completed => 'gray',
                        TripStatus::Cancelled => 'danger',
                    }),

                Tables\Columns\TextColumn::make('configuration')
                    ->label('Config')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('estimated_start_date')
                    ->label('Départ')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Membres')
                    ->counts('members'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'planned' => 'Planifié',
                        'active' => 'Actif',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
            DeparturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'view' => Pages\ViewTrip::route('/{record}'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
