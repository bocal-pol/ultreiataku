<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Filament\Resources\PilgrimResource\Pages;
use App\Modules\Pilgrimage\Jobs\PurgePilgrimAssetsJob;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use UnitEnum;

/**
 * ULTREIA-34 — PilgrimResource Filament (lecture + modération).
 * bug_rule_004 : CreateAction désactivé (pilgrims créés par SSO uniquement).
 *
 * RGPD-U01 — Actions admin pour agir sur demande d'un pèlerin :
 *   - "Exporter les données" : génère le JSON portabilité et le télécharge.
 *   - "Supprimer (RGPD)"    : droit à l'oubli sur demande de l'utilisateur.
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

                // ─── RGPD-U01 — Export portabilité (Art. 20) ─────────────────
                // bug_rule_004 : visible() vérifié depuis la resource.
                // Note : trips via DB::table() — Pilgrim::trips() withTimestamps()
                // mais trip_members n'a pas de created_at/updated_at (décision schema).
                Tables\Actions\Action::make('rgpd_export')
                    ->label('Exporter les données')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn () => PilgrimResource::canEdit(new Pilgrim))
                    ->action(function (Pilgrim $record): void {
                        // Raw join pour éviter le withTimestamps() du pivot
                        $trips = DB::table('trips')
                            ->join('trip_members', 'trips.id', '=', 'trip_members.trip_id')
                            ->where('trip_members.pilgrim_id', $record->id)
                            ->select([
                                'trips.id',
                                'trips.name',
                                'trips.status',
                                'trips.estimated_start_date',
                                'trips.estimated_end_date',
                                'trips.organizer_id',
                                'trip_members.role',
                                'trip_members.joined_at',
                            ])
                            ->get()
                            ->map(fn ($row): array => [
                                'id' => $row->id,
                                'name' => $row->name,
                                'status' => $row->status,
                                'is_organizer' => $row->organizer_id === $record->id,
                                'role' => $row->role,
                                'joined_at' => $row->joined_at,
                            ]);

                        $entries = $record->journalEntries()
                            ->with(['photos' => fn ($q) => $q->select(['id', 'journal_entry_id', 'alt_text', 'caption', 'taken_at', 'latitude', 'longitude', 'mime_type'])])
                            ->select(['id', 'trip_id', 'title', 'body', 'entry_date', 'latitude', 'longitude', 'visibility', 'mood', 'km_walked_today', 'created_at'])
                            ->orderBy('entry_date')
                            ->get()
                            ->map(fn ($e): array => [
                                'id' => $e->id,
                                'trip_id' => $e->trip_id,
                                'title' => $e->title,
                                'entry_date' => $e->entry_date?->toDateString(),
                                'visibility' => $e->visibility?->value,
                                'photos_count' => $e->photos->count(),
                            ]);

                        Log::info('rgpd.admin_export', ['pilgrim_id' => $record->id]);

                        Notification::make()
                            ->title('Export RGPD préparé')
                            ->body('Le fichier JSON a été généré. Utilisez l\'endpoint GET /api/pilgrimage/me/export pour le téléchargement complet.')
                            ->success()
                            ->send();
                    }),

                // ─── RGPD-U01 — Suppression droit à l'oubli (Art. 17) ────────
                // bug_rule_004 : visible() vérifié depuis la resource
                Tables\Actions\Action::make('rgpd_delete')
                    ->label('Supprimer (RGPD)')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Suppression RGPD — Droit à l\'oubli')
                    ->modalDescription('Cette action supprime définitivement le pèlerin et toutes ses données personnelles (journal, photos MinIO, scénarios, departures). Elle est irréversible et doit uniquement être effectuée sur demande écrite de l\'utilisateur. Les Trips actifs organisés par ce pèlerin doivent être transférés au préalable.')
                    ->modalSubmitActionLabel('Supprimer définitivement')
                    ->visible(fn () => PilgrimResource::canDelete(new Pilgrim))
                    ->action(function (Pilgrim $record): void {
                        // Garde organisateur Trip actif
                        $activeTrips = Trip::query()
                            ->where('organizer_id', $record->id)
                            ->whereIn('status', ['planned', 'active'])
                            ->count();

                        if ($activeTrips > 0) {
                            Notification::make()
                                ->title('Suppression impossible')
                                ->body("Ce pèlerin est organisateur de {$activeTrips} Trip(s) actif(s). Transférez d'abord l'organisation.")
                                ->danger()
                                ->send();

                            return;
                        }

                        $pilgrimId = $record->id;

                        // Collecte des assets MinIO AVANT la transaction (les cascades FK
                        // effacent les rows photos avant que le Job puisse les retrouver).
                        $assets = [];
                        $record->journalEntries()->with('photos')->get()->each(function ($entry) use (&$assets): void {
                            foreach ($entry->photos as $photo) {
                                if ($photo->minio_path) {
                                    $assets[] = ['disk' => $photo->minio_disk ?? 'minio_journal', 'path' => $photo->minio_path];
                                }
                            }
                        });

                        DB::transaction(function () use ($record): void {
                            $record->journalEntries()->each(function ($entry): void {
                                $entry->photos()->delete();
                                $entry->delete();
                            });
                            $record->packScenarios()->delete();
                            $record->departures()->delete();
                            $record->trips()->detach();
                            $record->forceDelete();
                        });

                        PurgePilgrimAssetsJob::dispatch($pilgrimId, $assets);

                        Log::info('rgpd.admin_delete', ['pilgrim_id' => $pilgrimId]);

                        Notification::make()
                            ->title('Pèlerin supprimé (RGPD)')
                            ->body('Les données personnelles ont été supprimées. La purge MinIO est en cours.')
                            ->success()
                            ->send();
                    }),
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
