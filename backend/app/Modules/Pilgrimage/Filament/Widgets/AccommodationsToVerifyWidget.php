<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Widgets;

use App\Modules\Pilgrimage\Models\Accommodation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

/**
 * Widget RG-08 — Hébergements dont verified_at est null ou > 6 mois.
 * Affiché sur le tableau de bord Filament Pilgrimage.
 */
class AccommodationsToVerifyWidget extends BaseWidget
{
    protected static ?string $heading = 'Hébergements à vérifier (RG-08)';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Accommodation::query()
                    ->where(function ($q) {
                        $q->whereNull('verified_at')
                            ->orWhere('verified_at', '<', now()->subMonths(6));
                    })
                    ->with('stage')
                    ->orderBy('verified_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('stage.code')->label('Étape')->sortable(),
                Tables\Columns\TextColumn::make('name.fr')->label('Nom')->limit(40),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Dernière vérif.')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) {
                            return 'Jamais vérifié';
                        }

                        return Carbon::parse($state)->format('d/m/Y') . ' (' . Carbon::parse($state)->diffForHumans() . ')';
                    })
                    ->badge()
                    ->color('warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('Valider maintenant')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['verified_at' => now()]))
                    ->requiresConfirmation()
                    ->modalHeading('Marquer comme vérifié ?')
                    ->modalDescription(fn ($record) => "Confirmer que l'hébergement « {$record->getTranslation('name', 'fr')} » a été contacté et les informations sont à jour."),
            ]);
    }
}
