<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\PackCategory;
use App\Modules\Pilgrimage\Filament\Resources\PackItemResource\Pages;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
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
 * ULTREIA-40 — PackItemResource Filament.
 *
 * Filtres : catégorie, is_shared, is_consumable.
 * bug_rule_004 : CreateAction avec ->visible().
 * Filament 4.12.3 conventions.
 */
class PackItemResource extends Resource
{
    protected static ?string $model = PackItem::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Item de sac';

    protected static ?string $pluralModelLabel = 'Items de sac';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Identification')->schema([
                Forms\Components\Select::make('pack_scenario_id')
                    ->label('Scénario')
                    ->options(PackScenario::query()
                        ->with('pilgrim')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => "{$s->pilgrim->display_name} — {$s->name}"]))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('category')
                    ->label('Catégorie')
                    ->options(collect(PackCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(200),

                Forms\Components\TextInput::make('brand')
                    ->label('Marque')
                    ->maxLength(100)
                    ->nullable(),

                Forms\Components\TextInput::make('model')
                    ->label('Modèle')
                    ->maxLength(100)
                    ->nullable(),

                Forms\Components\TextInput::make('weight_g')
                    ->label('Poids (g)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->step(1)
                    ->suffix('g'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Schemas\Components\Section::make('Options')->schema([
                Forms\Components\Toggle::make('is_shared')
                    ->label('Item mutualisé (duo)')
                    ->default(false),

                Forms\Components\Toggle::make('is_consumable')
                    ->label('Consommable (gaz, nourriture)')
                    ->default(false),

                Forms\Components\TextInput::make('replacement_km')
                    ->label('Remplacement (km)')
                    ->numeric()
                    ->nullable()
                    ->helperText('Kilométrage estimé avant remplacement'),
            ])->columns(3),

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->nullable()
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('packScenario.name')
                    ->label('Scénario')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Marque')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('weight_g')
                    ->label('Poids')
                    ->formatStateUsing(
                        fn (int $state): string => $state >= 1000
                        ? round($state / 1000, 1) . ' kg'
                        : $state . ' g'
                    )
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_consumable')
                    ->label('Conso.')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_shared')
                    ->label('Mutualisé')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(collect(PackCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),

                Tables\Filters\TernaryFilter::make('is_shared')
                    ->label('Mutualisé'),

                Tables\Filters\TernaryFilter::make('is_consumable')
                    ->label('Consommable'),
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
            ->defaultSort('category');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackItems::route('/'),
            'create' => Pages\CreatePackItem::route('/create'),
            'edit' => Pages\EditPackItem::route('/{record}/edit'),
        ];
    }
}
