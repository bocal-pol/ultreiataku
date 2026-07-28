<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use App\Modules\Pilgrimage\Enums\StageDifficulty;
use App\Modules\Pilgrimage\Filament\Resources\StageResource\Pages;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';

    protected static UnitEnum|string|null $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Étape';

    protected static ?string $pluralModelLabel = 'Étapes';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Étape')->schema([
                Forms\Components\Select::make('route_id')
                    ->label('Route')
                    ->options(PilgrimageRoute::pluck('slug', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(25)
                    ->helperText('Format XX-NN, ex: BE-01'),

                Forms\Components\TextInput::make('day_number')
                    ->label('Jour')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Forms\Components\Section::make('Variante')->schema([
                Forms\Components\Toggle::make('is_variant')
                    ->label('C\'est une variante d\'étape')
                    ->reactive()
                    ->default(false),

                Forms\Components\Select::make('parent_stage_id')
                    ->label('Étape parente')
                    ->options(Stage::where('is_variant', false)->pluck('code', 'id'))
                    ->searchable()
                    ->nullable()
                    ->visible(fn (Forms\Get $get) => (bool) $get('is_variant'))
                    ->helperText('Étape principale dont cette variante est un détour'),
            ])->columns(2),

            Forms\Components\Section::make('Traductions — Nom')->schema([
                Forms\Components\TextInput::make('name.fr')->label('Nom (FR)')->required(),
                Forms\Components\TextInput::make('name.nl')->label('Naam (NL)')->required(),
                Forms\Components\TextInput::make('name.de')->label('Name (DE)')->required(),
            ])->columns(3),

            Forms\Components\Section::make('Points géographiques')->schema([
                Forms\Components\Select::make('start_waypoint_id')
                    ->label('Point de départ')
                    ->options(Waypoint::pluck('slug', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('end_waypoint_id')
                    ->label('Point d\'arrivée')
                    ->options(Waypoint::pluck('slug', 'id'))
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Métriques')->schema([
                Forms\Components\TextInput::make('distance_km')
                    ->label('Distance (km)')
                    ->numeric()
                    ->step(0.01)
                    ->required(),

                Forms\Components\TextInput::make('elevation_gain_m')
                    ->label('D+ (m)')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('elevation_loss_m')
                    ->label('D- (m)')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('estimated_duration_h')
                    ->label('Durée estimée (h)')
                    ->numeric()
                    ->step(0.5),

                Forms\Components\Select::make('difficulty')
                    ->label('Difficulté')
                    ->options(collect(StageDifficulty::cases())->mapWithKeys(fn ($d) => [$d->value => $d->label()]))
                    ->default(StageDifficulty::Moderate->value),

                Forms\Components\Select::make('accommodation_type_default')
                    ->label('Hébergement par défaut')
                    ->options(collect(AccommodationType::cases())->mapWithKeys(fn ($a) => [$a->value => $a->label()]))
                    ->nullable(),
            ])->columns(3),

            Forms\Components\Section::make('Notes (i18n)')->schema([
                Forms\Components\Textarea::make('notes.fr')->label('Notes (FR)')->rows(3),
                Forms\Components\Textarea::make('notes.nl')->label('Notes (NL)')->rows(3),
                Forms\Components\Textarea::make('notes.de')->label('Notes (DE)')->rows(3),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('route.slug')->label('Route')->limit(20),
                Tables\Columns\TextColumn::make('day_number')->label('J')->sortable(),
                Tables\Columns\TextColumn::make('name.fr')->label('Nom')->limit(35),
                Tables\Columns\TextColumn::make('distance_km')->label('km')->sortable(),
                Tables\Columns\IconColumn::make('is_variant')
                    ->label('Variante')
                    ->boolean(),
                Tables\Columns\TextColumn::make('parentStage.code')
                    ->label('Parent')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn ($state) => StageDifficulty::from($state ?? 'moderate')->color()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('route_id')
                    ->label('Route')
                    ->options(PilgrimageRoute::pluck('slug', 'id')),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options(collect(StageDifficulty::cases())->mapWithKeys(fn ($d) => [$d->value => $d->label()])),
                Tables\Filters\TernaryFilter::make('is_variant')
                    ->label('Type')
                    ->trueLabel('Variantes uniquement')
                    ->falseLabel('Étapes principales uniquement')
                    ->placeholder('Toutes'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }
}
