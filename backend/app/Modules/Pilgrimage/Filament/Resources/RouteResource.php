<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Enums\Country;
use App\Modules\Pilgrimage\Filament\Resources\RouteResource\Pages;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RouteResource extends Resource
{
    protected static ?string $model = PilgrimageRoute::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map';

    protected static UnitEnum|string|null $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Route';

    protected static ?string $pluralModelLabel = 'Routes';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informations générales')->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('Identifiant kebab-case, ex: via-mosana-belgique'),

                Forms\Components\Select::make('country')
                    ->label('Pays')
                    ->options(collect(Country::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->required()
                    ->default(Country::BE->value),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre d\'affichage')
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Forms\Components\Section::make('Traductions — Nom')->schema([
                Forms\Components\TextInput::make('name.fr')->label('Nom (FR)')->required()->maxLength(200),
                Forms\Components\TextInput::make('name.nl')->label('Naam (NL)')->required()->maxLength(200),
                Forms\Components\TextInput::make('name.de')->label('Name (DE)')->required()->maxLength(200),
            ])->columns(3),

            Forms\Components\Section::make('Traductions — Description')->schema([
                Forms\Components\Textarea::make('description.fr')->label('Description (FR)')->rows(3),
                Forms\Components\Textarea::make('description.nl')->label('Beschrijving (NL)')->rows(3),
                Forms\Components\Textarea::make('description.de')->label('Beschreibung (DE)')->rows(3),
            ])->columns(3),

            Forms\Components\Section::make('Métriques')->schema([
                Forms\Components\TextInput::make('total_distance_km')
                    ->label('Distance totale (km)')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),

                Forms\Components\TextInput::make('total_elevation_gain_m')
                    ->label('Dénivelé positif total (m)')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country')->badge(),
                Tables\Columns\TextColumn::make('name.fr')->label('Nom')->limit(40),
                Tables\Columns\TextColumn::make('total_distance_km')->label('km'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->options(collect(Country::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoutes::route('/'),
            'create' => Pages\CreateRoute::route('/create'),
            'edit' => Pages\EditRoute::route('/{record}/edit'),
        ];
    }
}
