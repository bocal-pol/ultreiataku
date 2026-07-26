<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Enums\DetourType;
use App\Modules\Pilgrimage\Enums\PoiCategory;
use App\Modules\Pilgrimage\Enums\WaypointType;
use App\Modules\Pilgrimage\Filament\Resources\WaypointResource\Pages;
use App\Modules\Pilgrimage\Models\Waypoint;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WaypointResource extends Resource
{
    protected static ?string $model = Waypoint::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';

    protected static UnitEnum|string|null $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Waypoint / POI';

    protected static ?string $pluralModelLabel = 'Waypoints & POI';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Identification')->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(150),

                Forms\Components\Select::make('type')
                    ->options(collect(WaypointType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                    ->required()
                    ->default(WaypointType::City->value)
                    ->reactive(),

                Forms\Components\Select::make('poi_category')
                    ->label('Catégorie POI')
                    ->options(collect(PoiCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->nullable()
                    ->visible(fn ($get) => $get('type') === 'poi'),

                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Traductions — Nom')->schema([
                Forms\Components\TextInput::make('name.fr')->label('Nom (FR)')->required(),
                Forms\Components\TextInput::make('name.nl')->label('Naam (NL)')->required(),
                Forms\Components\TextInput::make('name.de')->label('Name (DE)')->required(),
            ])->columns(3),

            Forms\Components\Section::make('Coordonnées GPS')->schema([
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->required()
                    ->step(0.0000001),

                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->required()
                    ->step(0.0000001),
            ])->columns(2),

            Forms\Components\Section::make('Détour')->schema([
                Forms\Components\Select::make('detour_type')
                    ->options(collect(DetourType::cases())->mapWithKeys(fn ($d) => [$d->value => $d->label()]))
                    ->nullable(),

                Forms\Components\TextInput::make('detour_distance_km')
                    ->label('Distance détour (km)')
                    ->numeric()
                    ->step(0.01)
                    ->nullable(),

                Forms\Components\TextInput::make('detour_duration_min')
                    ->label('Durée détour (min)')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('visit_duration_min')
                    ->label('Durée visite (min)')
                    ->numeric()
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Accès & tarifs')->schema([
                Forms\Components\TextInput::make('entry_cost_eur')
                    ->label('Coût entrée (€)')
                    ->numeric()
                    ->step(0.01)
                    ->nullable(),

                Forms\Components\Toggle::make('booking_required')
                    ->label('Réservation requise')
                    ->default(false),

                Forms\Components\TextInput::make('booking_contact')
                    ->label('Contact réservation')
                    ->maxLength(255)
                    ->nullable(),
            ])->columns(3),

            Forms\Components\Section::make('Description (i18n)')->schema([
                Forms\Components\Textarea::make('description.fr')->label('Description (FR)')->rows(3),
                Forms\Components\Textarea::make('description.nl')->label('Beschrijving (NL)')->rows(3),
                Forms\Components\Textarea::make('description.de')->label('Beschreibung (DE)')->rows(3),
            ])->columns(3),

            Forms\Components\Section::make('Vérification')->schema([
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Vérifié le')
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('name.fr')->label('Nom')->limit(30),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('poi_category')->label('Catégorie')->badge(),
                Tables\Columns\TextColumn::make('latitude')->label('Lat'),
                Tables\Columns\TextColumn::make('longitude')->label('Lon'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime()->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(WaypointType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\SelectFilter::make('poi_category')
                    ->options(collect(PoiCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('slug');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaypoints::route('/'),
            'create' => Pages\CreateWaypoint::route('/create'),
            'edit' => Pages\EditWaypoint::route('/{record}/edit'),
        ];
    }
}
