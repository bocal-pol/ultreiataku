<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Enums\JournalMood;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Filament\Resources\JournalEntryResource\Pages;
use App\Modules\Pilgrimage\Filament\Resources\JournalEntryResource\RelationManagers\PhotosRelationManager;
use App\Modules\Pilgrimage\Models\JournalEntry;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ULTREIA-54 — JournalEntryResource Filament (lecture + modération).
 *
 * Specs §5.1 : lecture + modération ; filtres trip/visibility/pilgrim ; badge photos.
 * bug_rule_004 : CreateAction visible() appliqué sur ListJournalEntries.
 * Pattern Filament 4.12.3 :
 *   - form(Schema $schema): Schema
 *   - BackedEnum|string|null $navigationIcon
 *   - UnitEnum|string|null $navigationGroup
 */
class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Entrée journal';

    protected static ?string $pluralModelLabel = 'Carnet de voyage';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('trip_id')
                ->label('Trip')
                ->relationship('trip', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpan(2),

            Forms\Components\Select::make('pilgrim_id')
                ->label('Auteur')
                ->relationship('pilgrim', 'display_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('stage_id')
                ->label('Étape')
                ->relationship('stage', 'code')
                ->searchable()
                ->nullable(),

            Forms\Components\TextInput::make('title')
                ->label('Titre')
                ->maxLength(300)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('body')
                ->label('Texte')
                ->rows(6)
                ->columnSpanFull(),

            Forms\Components\DatePicker::make('entry_date')
                ->label('Date')
                ->required(),

            Forms\Components\Select::make('visibility')
                ->label('Visibilité')
                ->options([
                    JournalVisibility::Private->value => 'Privée',
                    JournalVisibility::Members->value => 'Membres',
                    JournalVisibility::Public->value  => 'Publique',
                ])
                ->required(),

            Forms\Components\Select::make('mood')
                ->label('Humeur')
                ->options([
                    JournalMood::Great->value     => 'Super 😄',
                    JournalMood::Good->value      => 'Bien 🙂',
                    JournalMood::Neutral->value   => 'Neutre 😐',
                    JournalMood::Tired->value     => 'Fatigué 😴',
                    JournalMood::Difficult->value => 'Difficile 😓',
                ])
                ->nullable(),

            Forms\Components\TextInput::make('km_walked_today')
                ->label('Km parcourus aujourd\'hui')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('km'),

            Forms\Components\Toggle::make('is_synced')
                ->label('Synchronisé')
                ->default(true),

            Forms\Components\TextInput::make('local_id')
                ->label('Local ID (offline)')
                ->maxLength(36)
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pilgrim.display_name')
                    ->label('Auteur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trip.name')
                    ->label('Trip')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stage.code')
                    ->label('Étape')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibilité')
                    ->badge()
                    ->color(fn (JournalVisibility $state): string => $state->color())
                    ->formatStateUsing(fn (JournalVisibility $state): string => $state->label()),

                Tables\Columns\TextColumn::make('mood')
                    ->label('Humeur')
                    ->formatStateUsing(fn (?JournalMood $state): string => $state?->emoji() ?? '—'),

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Photos')
                    ->counts('photos')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_synced')
                    ->label('Sync')
                    ->boolean(),

                Tables\Columns\TextColumn::make('km_walked_today')
                    ->label('Km')
                    ->suffix(' km')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trip_id')
                    ->label('Trip')
                    ->relationship('trip', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibilité')
                    ->options([
                        JournalVisibility::Private->value => 'Privée',
                        JournalVisibility::Members->value => 'Membres',
                        JournalVisibility::Public->value  => 'Publique',
                    ]),

                Tables\Filters\SelectFilter::make('pilgrim_id')
                    ->label('Auteur')
                    ->relationship('pilgrim', 'display_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_synced')
                    ->label('Synchronisé'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('entry_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListJournalEntries::route('/'),
            'view'   => Pages\ViewJournalEntry::route('/{record}'),
            'edit'   => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
