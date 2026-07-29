<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\JournalEntryResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ULTREIA-54 — RelationManager photos sur JournalEntryResource.
 *
 * Lecture et modération uniquement (pas d'upload depuis Filament — proxy API).
 * L'admin peut voir les métadonnées et supprimer une photo.
 */
class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Photos';

    protected static ?string $pluralModelLabel = 'Photos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->limit(8)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Alt text')
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(40),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('file_size_bytes')
                    ->label('Taille')
                    ->formatStateUsing(fn (int $state): string => round($state / 1024 / 1024, 1) . ' Mo'),

                Tables\Columns\IconColumn::make('is_synced')
                    ->label('Sync')
                    ->boolean(),

                Tables\Columns\TextColumn::make('taken_at')
                    ->label('Prise le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
