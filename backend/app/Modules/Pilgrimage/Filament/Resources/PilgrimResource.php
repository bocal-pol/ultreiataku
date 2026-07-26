<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Filament\Resources\PilgrimResource\Pages;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ULTREIA-34 — PilgrimResource Filament (lecture + modération).
 * bug_rule_004 : CreateAction désactivé (pilgrims créés par SSO uniquement).
 */
class PilgrimResource extends Resource
{
    protected static ?string $model = Pilgrim::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Pèlerin';

    protected static ?string $pluralModelLabel = 'Pèlerins';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('display_name')
                ->label('Nom affiché')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('user_id')
                ->label('User ID (SSO)')
                ->disabled()
                ->numeric(),

            Forms\Components\Select::make('preferred_locale')
                ->label('Langue')
                ->options(['fr' => 'Français', 'nl' => 'Nederlands', 'de' => 'Deutsch']),

            Forms\Components\Select::make('configuration')
                ->label('Configuration')
                ->options(['solo' => 'Solo', 'duo' => 'Duo']),

            Forms\Components\TextInput::make('target_base_weight_kg')
                ->label('Poids cible sac (kg)')
                ->numeric()
                ->step(0.1),

            Forms\Components\TextInput::make('target_daily_kcal')
                ->label('Apport kcal/jour cible')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID (SSO)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('preferred_locale')
                    ->label('Langue')
                    ->badge(),

                Tables\Columns\TextColumn::make('configuration')
                    ->label('Config')
                    ->badge(),

                Tables\Columns\TextColumn::make('organizedTrips_count')
                    ->label('Trips organisés')
                    ->counts('organizedTrips'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('configuration')
                    ->options(['solo' => 'Solo', 'duo' => 'Duo']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPilgrims::route('/'),
            'view' => Pages\ViewPilgrim::route('/{record}'),
            'edit' => Pages\EditPilgrim::route('/{record}/edit'),
        ];
    }
}
