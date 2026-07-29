<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource\RelationManagers;

use App\Modules\Pilgrimage\Enums\PackCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * RelationManager PackItems pour PackScenarioResource.
 *
 * Affiche les items du scénario avec leurs poids et totaux.
 */
class PackItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items du sac';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Item')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(200),

                Forms\Components\Select::make('category')
                    ->label('Catégorie')
                    ->options(collect(PackCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->required(),

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
            ])->columns(3),

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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ord.')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(collect(PackCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),

                Tables\Filters\TernaryFilter::make('is_consumable')
                    ->label('Consommable'),

                Tables\Filters\TernaryFilter::make('is_shared')
                    ->label('Mutualisé'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => PackItemsRelationManager::canCreate()),
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
            ->defaultSort('category')
            ->defaultSort('sort_order');
    }
}
