<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\PackSeason;
use App\Modules\Pilgrimage\Enums\PilgrimConfiguration;
use App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource\Pages;
use App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource\RelationManagers\PackItemsRelationManager;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ULTREIA-40/43 — PackScenarioResource Filament.
 *
 * bug_rule_004 : CreateAction avec ->visible(fn () => canCreate()) sur la liste.
 * Filament 4.12.3 :
 *   - form(Schema $schema): Schema
 *   - BackedEnum|string|null $navigationIcon
 *   - UnitEnum|string|null $navigationGroup
 *   - use BackedEnum; use UnitEnum;
 */
class PackScenarioResource extends Resource
{
    protected static ?string $model = PackScenario::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Scénario de sac';

    protected static ?string $pluralModelLabel = 'Scénarios de sac';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Identification')->schema([
                Forms\Components\Select::make('pilgrim_id')
                    ->label('Pèlerin')
                    ->options(Pilgrim::orderBy('display_name')->pluck('display_name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nom du scénario')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),

                Forms\Components\Select::make('configuration')
                    ->label('Configuration')
                    ->options(collect(PilgrimConfiguration::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))
                    ->default(PilgrimConfiguration::Solo->value)
                    ->required(),

                Forms\Components\Select::make('season')
                    ->label('Saison')
                    ->options(collect(PackSeason::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(PackSeason::Spring->value)
                    ->required(),

                Forms\Components\TextInput::make('target_base_weight_kg')
                    ->label('Objectif poids de base (kg)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(1)
                    ->maxValue(30)
                    ->nullable()
                    ->suffix('kg')
                    ->helperText('RG-01 : vert ≤ objectif, orange ≤ +1 kg, rouge > +1 kg'),
            ])->columns(2),

            Schemas\Components\Section::make('Description')->schema([
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pilgrim.display_name')
                    ->label('Pèlerin')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Scénario')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('configuration')
                    ->label('Config')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->value ? ucfirst($state->value) : '—'),

                Tables\Columns\TextColumn::make('season')
                    ->label('Saison')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\TextColumn::make('target_base_weight_kg')
                    ->label('Objectif')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' kg' : '—')
                    ->sortable(),

                // Totaux calculés (non persistés — lus depuis items)
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('configuration')
                    ->label('Configuration')
                    ->options(collect(PilgrimConfiguration::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),

                Tables\Filters\SelectFilter::make('season')
                    ->label('Saison')
                    ->options(collect(PackSeason::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PackItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackScenarios::route('/'),
            'create' => Pages\CreatePackScenario::route('/create'),
            'edit' => Pages\EditPackScenario::route('/{record}/edit'),
        ];
    }
}
