<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\MealContext;
use App\Modules\Pilgrimage\Enums\MealType;
use App\Modules\Pilgrimage\Filament\Resources\MealResource\Pages;
use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cake';

    protected static UnitEnum|string|null $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Repas';

    protected static ?string $pluralModelLabel = 'Repas';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Identification')->schema([
                Forms\Components\Select::make('stage_id')
                    ->label('Étape')
                    ->options(Stage::orderBy('sort_order')->pluck('code', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('meal_type')
                    ->label('Type de repas')
                    ->options(collect(MealType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                    ->required()
                    ->default(MealType::Dinner->value),

                Forms\Components\Select::make('meal_context')
                    ->label('Contexte')
                    ->options(collect(MealContext::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->required()
                    ->default(MealContext::Restaurant->value),
            ])->columns(3),

            Schemas\Components\Section::make('Nom (i18n)')->schema([
                Forms\Components\TextInput::make('name.fr')->label('Nom (FR)')->required()->maxLength(300),
                Forms\Components\TextInput::make('name.nl')->label('Naam (NL)')->required()->maxLength(300),
                Forms\Components\TextInput::make('name.de')->label('Name (DE)')->required()->maxLength(300),
            ])->columns(3),

            Schemas\Components\Section::make('Description (i18n)')->schema([
                Forms\Components\Textarea::make('description.fr')->label('Description (FR)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('description.nl')->label('Beschrijving (NL)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('description.de')->label('Beschreibung (DE)')->rows(2)->nullable(),
            ])->columns(3),

            Schemas\Components\Section::make('Restaurant')->schema([
                Forms\Components\TextInput::make('restaurant_name')->label('Nom restaurant')->maxLength(200)->nullable(),
                Forms\Components\TextInput::make('restaurant_address')->label('Adresse restaurant')->maxLength(300)->nullable(),
            ])->columns(2),

            Schemas\Components\Section::make('Métriques')->schema([
                Forms\Components\TextInput::make('price_estimate_eur')->label('Prix estimé (€)')->numeric()->step(0.01)->nullable(),
                Forms\Components\TextInput::make('kcal_estimate')->label('Calories estimées (kcal)')->numeric()->nullable(),
                Forms\Components\TextInput::make('weight_g')->label('Poids portée (g)')->numeric()->nullable()
                    ->helperText('Pour repas bivouac portés uniquement'),
            ])->columns(3),

            Schemas\Components\Section::make('Notes (i18n)')->schema([
                Forms\Components\Textarea::make('notes.fr')->label('Notes (FR)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('notes.nl')->label('Notities (NL)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('notes.de')->label('Hinweise (DE)')->rows(2)->nullable(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.code')
                    ->label('Étape')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('meal_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Nom')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('meal_context')
                    ->label('Contexte')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\TextColumn::make('restaurant_name')
                    ->label('Restaurant')
                    ->limit(25),

                Tables\Columns\TextColumn::make('price_estimate_eur')
                    ->label('Prix')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('kcal_estimate')
                    ->label('kcal'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meal_type')
                    ->label('Type de repas')
                    ->options(collect(MealType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\SelectFilter::make('meal_context')
                    ->label('Contexte')
                    ->options(collect(MealContext::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('stage_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'edit' => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
