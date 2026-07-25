<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use App\Modules\Pilgrimage\Filament\Resources\AccommodationResource\Pages;
use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Hébergement';

    protected static ?string $pluralModelLabel = 'Hébergements';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identification')->schema([
                Forms\Components\Select::make('stage_id')
                    ->label('Étape')
                    ->options(Stage::orderBy('sort_order')->pluck('code', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options(collect(AccommodationType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                    ->required()
                    ->default(AccommodationType::Gite->value),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Hébergement principal')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
            ])->columns(2),

            Forms\Components\Section::make('Nom (i18n)')->schema([
                Forms\Components\TextInput::make('name.fr')->label('Nom (FR)')->required()->maxLength(200),
                Forms\Components\TextInput::make('name.nl')->label('Naam (NL)')->required()->maxLength(200),
                Forms\Components\TextInput::make('name.de')->label('Name (DE)')->required()->maxLength(200),
            ])->columns(3),

            Forms\Components\Section::make('Contact & localisation')->schema([
                Forms\Components\TextInput::make('address')->label('Adresse')->maxLength(500)->nullable()->columnSpanFull(),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->maxLength(30)->nullable(),
                Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255)->nullable(),
                Forms\Components\TextInput::make('website')->label('Site web')->url()->maxLength(255)->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Tarifs & capacité')->schema([
                Forms\Components\TextInput::make('price_min_eur')->label('Prix min (€)')->numeric()->step(0.01)->nullable(),
                Forms\Components\TextInput::make('price_max_eur')->label('Prix max (€)')->numeric()->step(0.01)->nullable(),
                Forms\Components\Toggle::make('is_donativo')->label('Donativo')->default(false),
                Forms\Components\TextInput::make('capacity')->label('Capacité (places)')->numeric()->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Équipements')->schema([
                Forms\Components\Toggle::make('has_shower')->label('Douche')->default(false),
                Forms\Components\Toggle::make('has_kitchen')->label('Cuisine')->default(false),
                Forms\Components\Toggle::make('has_wifi')->label('WiFi')->default(false),
                Forms\Components\Toggle::make('stamps_credencial')->label('Tampon crédential')->default(false),
                Forms\Components\Toggle::make('pilgrim_friendly')->label('Pèlerin friendly')->default(true),
            ])->columns(3),

            Forms\Components\Section::make('Réservation')->schema([
                Forms\Components\Toggle::make('booking_required')->label('Réservation requise')->default(false),
                Forms\Components\TextInput::make('booking_notice_days')->label('Préavis (jours)')->numeric()->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Bivouac')->schema([
                Forms\Components\Toggle::make('bivouac_legal')->label('Bivouac légal')->default(false),
                Forms\Components\Textarea::make('bivouac_notes.fr')->label('Notes bivouac (FR)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('bivouac_notes.nl')->label('Bivouac notities (NL)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('bivouac_notes.de')->label('Biwak-Notizen (DE)')->rows(2)->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Notes (i18n)')->schema([
                Forms\Components\Textarea::make('notes.fr')->label('Notes (FR)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('notes.nl')->label('Notities (NL)')->rows(2)->nullable(),
                Forms\Components\Textarea::make('notes.de')->label('Hinweise (DE)')->rows(2)->nullable(),
            ])->columns(3),

            Forms\Components\Section::make('Vérification — RG-08')->schema([
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Vérifié le')
                    ->nullable()
                    ->helperText('Laissé vide = à vérifier. > 6 mois = badge orange dans le tableau de bord.'),
            ]),
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

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Nom')
                    ->limit(35)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_shower')->label('Douche')->boolean(),
                Tables\Columns\IconColumn::make('has_kitchen')->label('Cuisine')->boolean(),
                Tables\Columns\IconColumn::make('stamps_credencial')->label('Tampon')->boolean(),
                Tables\Columns\IconColumn::make('bivouac_legal')->label('Bivouac').boolean(),

                Tables\Columns\TextColumn::make('price_min_eur')
                    ->label('Prix min')
                    ->money('EUR')
                    ->sortable(),

                // Badge RG-08 : orange si verified_at null ou > 6 mois
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Vérifié le')
                    ->dateTime('d/m/Y')
                    ->badge()
                    ->color(fn ($record) => $record !== null && $record->isObsolete() ? 'warning' : 'success')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record !== null && $record->isObsolete()) {
                            return 'À vérifier';
                        }

                        return $state ? Carbon::parse($state)->format('d/m/Y') : '—';
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(AccommodationType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\TernaryFilter::make('is_primary')->label('Principal'),
                Tables\Filters\TernaryFilter::make('bivouac_legal')->label('Bivouac légal'),
                Tables\Filters\Filter::make('to_verify')
                    ->label('À vérifier (RG-08)')
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->whereNull('verified_at')
                            ->orWhere('verified_at', '<', now()->subMonths(6));
                    })),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isObsolete())
                    ->action(fn ($record) => $record->update(['verified_at' => now()]))
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('stage_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccommodations::route('/'),
            'create' => Pages\CreateAccommodation::route('/create'),
            'edit' => Pages\EditAccommodation::route('/{record}/edit'),
        ];
    }
}
