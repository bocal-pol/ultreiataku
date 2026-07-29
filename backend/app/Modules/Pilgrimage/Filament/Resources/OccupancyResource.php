<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Filament\Resources\OccupancyResource\Pages;
use App\Modules\Pilgrimage\Jobs\RebuildOccupancyForTripJob;
use App\Modules\Pilgrimage\Models\Occupancy;
use App\Modules\Pilgrimage\Models\Trip;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

/**
 * ULTREIA-34 — OccupancyResource Filament.
 * Table matérialisée ADR-U03 — LECTURE SEULE.
 * Pas de create/edit/delete direct ; bouton Rebuild via artisan pilgrimage:occupancy:rebuild.
 * bug_rule_004 : pas de CreateAction (table en lecture seule).
 */
class OccupancyResource extends Resource
{
    protected static ?string $model = Occupancy::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Occupancy';

    protected static ?string $pluralModelLabel = 'Occupancies';

    protected static ?int $navigationSort = 13;

    /** Pas de création directe — table gérée par OccupancyObserver (ADR-U03). */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        // Lecture seule uniquement — formulaire minimal
        return $schema->components([
            Forms\Components\TextInput::make('accommodation_id')
                ->label('Hébergement ID')
                ->disabled(),
            Forms\Components\TextInput::make('trip_id')
                ->label('Trip ID')
                ->disabled(),
            Forms\Components\DatePicker::make('date')
                ->label('Date')
                ->disabled(),
            Forms\Components\TextInput::make('count')
                ->label('Nombre de pèlerins')
                ->numeric()
                ->disabled(),
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

                Tables\Columns\TextColumn::make('accommodation.name')
                    ->label('Hébergement')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->accommodation?->getTranslation('name', 'fr') ?? $record->accommodation_id),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('count')
                    ->label('Pèlerins')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 5 => 'danger',
                        $state >= 3 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Calculé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trip_id')
                    ->label('Trip')
                    ->relationship('trip', 'name')
                    ->searchable(),
            ])
            ->headerActions([
                // Bouton Rebuild global — ADR-U03
                Action::make('rebuild_all')
                    ->label('Recalculer tout')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Recalculer toutes les occupancies')
                    ->modalDescription('Lance la commande pilgrimage:occupancy:rebuild pour tous les Trips. Cela peut prendre quelques secondes.')
                    ->action(function (): void {
                        Artisan::call('pilgrimage:occupancy:rebuild');
                    })
                    ->successNotificationTitle('Occupancies recalculées'),
            ])
            ->recordActions([
                // Rebuild pour un Trip spécifique via le trip_id de l'occupancy
                Action::make('rebuild_trip')
                    ->label('Recalculer Trip')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(fn (Occupancy $record) => RebuildOccupancyForTripJob::dispatchSync($record->trip_id))
                    ->successNotificationTitle('Occupancy du Trip recalculée'),

                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOccupancies::route('/'),
            'view' => Pages\ViewOccupancy::route('/{record}'),
        ];
    }
}
