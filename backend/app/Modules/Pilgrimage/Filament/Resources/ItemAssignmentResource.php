<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource\Pages;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Stage;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ULTREIA-41 — ItemAssignmentResource Filament.
 *
 * Filtres : departure, pilgrim assigné.
 * bug_rule_004 : CreateAction avec ->visible().
 * Filament 4.12.3 conventions.
 */
class ItemAssignmentResource extends Resource
{
    protected static ?string $model = ItemAssignment::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static UnitEnum|string|null $navigationGroup = 'Voyages';

    protected static ?string $modelLabel = 'Assignation d\'item';

    protected static ?string $pluralModelLabel = 'Assignations d\'items';

    protected static ?int $navigationSort = 22;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Assignation')->schema([
                Forms\Components\Select::make('pack_item_id')
                    ->label('Item de sac')
                    ->options(PackItem::query()
                        ->with('packScenario')
                        ->get()
                        ->mapWithKeys(fn ($item) => [
                            $item->id => "{$item->packScenario?->name} — {$item->name} ({$item->weight_g} g)",
                        ]))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('departure_id')
                    ->label('Départ')
                    ->options(Departure::query()
                        ->with(['pilgrim', 'startStage', 'endStage'])
                        ->get()
                        ->mapWithKeys(fn ($d) => [
                            $d->id => "{$d->pilgrim?->display_name} — {$d->startStage?->code} → {$d->endStage?->code} ({$d->planned_start_date?->format('d/m/Y')})",
                        ]))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('assigned_to_pilgrim_id')
                    ->label('Attribué à')
                    ->options(Pilgrim::orderBy('display_name')->pluck('display_name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('from_stage_id')
                    ->label('Depuis l\'étape')
                    ->options(Stage::orderBy('sort_order')->pluck('code', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('to_stage_id')
                    ->label("Jusqu'à l'étape")
                    ->options(Stage::orderBy('sort_order')->pluck('code', 'id'))
                    ->searchable()
                    ->nullable(),
            ])->columns(2),

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
                Tables\Columns\TextColumn::make('packItem.name')
                    ->label('Item')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('packItem.category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\TextColumn::make('assignedTo.display_name')
                    ->label('Attribué à')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('departure.pilgrim.display_name')
                    ->label('Départ (pèlerin)')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fromStage.code')
                    ->label('De')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('toStage.code')
                    ->label('À')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departure_id')
                    ->label('Départ')
                    ->options(Departure::query()
                        ->with(['pilgrim', 'startStage'])
                        ->get()
                        ->mapWithKeys(fn ($d) => [
                            $d->id => "{$d->pilgrim?->display_name} — {$d->startStage?->code} ({$d->planned_start_date?->format('d/m/Y')})",
                        ])),

                Tables\Filters\SelectFilter::make('assigned_to_pilgrim_id')
                    ->label('Pèlerin assigné')
                    ->options(Pilgrim::orderBy('display_name')->pluck('display_name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemAssignments::route('/'),
            'create' => Pages\CreateItemAssignment::route('/create'),
            'edit' => Pages\EditItemAssignment::route('/{record}/edit'),
        ];
    }
}
