<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Modules\Pilgrimage\Enums\GpxPrecision;
use App\Modules\Pilgrimage\Enums\GpxTraceType;
use App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource\Pages;
use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use App\Modules\Pilgrimage\Services\GpxImportService;
use App\Modules\Pilgrimage\Support\GpxXmlParser;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class GpxTraceResource extends Resource
{
    protected static ?string $model = GpxTrace::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static UnitEnum|string|null $navigationGroup = 'Pèlerinage';

    protected static ?string $modelLabel = 'Trace GPX';

    protected static ?string $pluralModelLabel = 'Traces GPX';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Identification')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(200),

                Forms\Components\Select::make('trace_type')
                    ->label('Type de trace')
                    ->options(collect(GpxTraceType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                    ->required()
                    ->default(GpxTraceType::StageMain->value),

                Forms\Components\Select::make('precision')
                    ->label('Précision')
                    ->options(collect(GpxPrecision::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                    ->default(GpxPrecision::Approximate->value),

                Forms\Components\TextInput::make('source')
                    ->label('Source')
                    ->maxLength(200)
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Association')->schema([
                Forms\Components\Select::make('stage_id')
                    ->label('Étape associée')
                    ->options(Stage::pluck('code', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('waypoint_id')
                    ->label('Waypoint associé (détour)')
                    ->options(Waypoint::pluck('slug', 'id'))
                    ->searchable()
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Import GPX')->schema([
                Forms\Components\FileUpload::make('gpx_file')
                    ->label('Fichier GPX')
                    ->disk('local')
                    ->directory('tmp/gpx-uploads')
                    ->acceptedFileTypes(['application/gpx+xml', 'application/octet-stream', 'text/xml'])
                    ->helperText('Fichier .gpx contenant au moins un <trk> ou <rte>')
                    ->validationMessages([
                        'required' => 'Un fichier GPX est requis à la création.',
                    ]),
            ]),

            Forms\Components\Section::make('Métadonnées (auto-calculées à l\'import)')->schema([
                Forms\Components\TextInput::make('distance_km')->label('Distance (km)')->numeric()->disabled(),
                Forms\Components\TextInput::make('elevation_gain_m')->label('D+ (m)')->numeric()->disabled(),
                Forms\Components\TextInput::make('elevation_loss_m')->label('D- (m)')->numeric()->disabled(),
                Forms\Components\TextInput::make('track_points_count')->label('Points GPS')->numeric()->disabled(),
            ])->columns(4)->hiddenOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('stage.code')->label('Étape')->badge(),
                Tables\Columns\TextColumn::make('trace_type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'stage_main' => 'primary',
                        'detour' => 'warning',
                        'variant' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('distance_km')->label('km'),
                Tables\Columns\TextColumn::make('elevation_gain_m')->label('D+'),
                Tables\Columns\TextColumn::make('track_points_count')->label('pts'),
                Tables\Columns\TextColumn::make('precision')->badge(),
                Tables\Columns\TextColumn::make('imported_at')->dateTime()->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trace_type')
                    ->options(collect(GpxTraceType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\SelectFilter::make('stage_id')
                    ->label('Étape')
                    ->options(Stage::pluck('code', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('imported_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGpxTraces::route('/'),
            'create' => Pages\CreateGpxTrace::route('/create'),
            'edit' => Pages\EditGpxTrace::route('/{record}/edit'),
        ];
    }
}
