<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources;

use App\Modules\Pilgrimage\Enums\GuideCategory;
use App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource\Pages;
use App\Modules\Pilgrimage\Models\GuideSection;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class GuideSectionResource extends Resource
{
    protected static ?string $model = GuideSection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static UnitEnum|string|null $navigationGroup = 'Contenu';

    protected static ?string $modelLabel = 'Section guide';

    protected static ?string $pluralModelLabel = 'Sections guide';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Identification')->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->helperText('Identifiant unique : forme-physique, credencial-et-papiers…'),

                Forms\Components\Select::make('category')
                    ->label('Catégorie')
                    ->options(collect(GuideCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]))
                    ->required()
                    ->default(GuideCategory::Pratique->value),

                Forms\Components\TextInput::make('icon')
                    ->label('Icône Heroicon')
                    ->required()
                    ->maxLength(100)
                    ->default('heroicon-o-book-open')
                    ->helperText('Ex: heroicon-o-heart, heroicon-o-map, heroicon-o-sun'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre d\'affichage')
                    ->numeric()
                    ->required()
                    ->default(0),

                Forms\Components\Toggle::make('is_published')
                    ->label('Publié')
                    ->default(true)
                    ->helperText('Section visible sur le Chemin pour tous les pèlerins'),
            ])->columns(2),

            Schemas\Components\Section::make('Titre (i18n — vocabulaire pèlerin)')->schema([
                Forms\Components\TextInput::make('title.fr')
                    ->label('Titre (FR)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title.nl')
                    ->label('Titel (NL)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title.de')
                    ->label('Titel (DE)')
                    ->required()
                    ->maxLength(255),
            ])->columns(3),

            Schemas\Components\Section::make('Contenu Markdown (i18n — la parole du pèlerin)')->schema([
                Forms\Components\Textarea::make('content.fr')
                    ->label('Contenu (FR — Markdown)')
                    ->required()
                    ->rows(20)
                    ->helperText('Markdown. Parlez le chemin : « pèlerin », « étape », « crédential »…'),
                Forms\Components\Textarea::make('content.nl')
                    ->label('Inhoud (NL — Markdown)')
                    ->required()
                    ->rows(20)
                    ->helperText('TODO: traduction NL à compléter par l\'équipe.'),
                Forms\Components\Textarea::make('content.de')
                    ->label('Inhalt (DE — Markdown)')
                    ->required()
                    ->rows(20)
                    ->helperText('TODO: Übersetzung DE durch das Team vervollständigen.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('title.fr')
                    ->label('Titre (FR)')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(collect(GuideCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideSections::route('/'),
            'create' => Pages\CreateGuideSection::route('/create'),
            'edit' => Pages\EditGuideSection::route('/{record}/edit'),
        ];
    }
}
