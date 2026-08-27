<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageResource\Pages;
use App\Models\HomePage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomePageResource extends Resource
{
    protected static ?string $model = HomePage::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $modelLabel = 'Inicio';

    protected static ?string $pluralModelLabel = 'Inicio';

    protected static ?string $navigationLabel = 'Portada';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Secciones')
                ->tabs([

                    // ── PORTADA (HERO) ──────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Portada')
                        ->icon('heroicon-o-star')
                        ->schema([
                            Forms\Components\TextInput::make('hero_title')
                                ->label('Título principal')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('hero_description')
                                ->label('Descripción')
                                ->rows(3)
                                ->columnSpanFull(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('hero_button_1_text')
                                    ->label('Botón 1 – Texto'),
                                Forms\Components\TextInput::make('hero_button_1_link')
                                    ->label('Botón 1 – Link (ej: /profesionales)'),
                                Forms\Components\TextInput::make('hero_button_2_text')
                                    ->label('Botón 2 – Texto'),
                                Forms\Components\TextInput::make('hero_button_2_link')
                                    ->label('Botón 2 – Link (ej: /especialidades/ginecologia)'),
                            ]),
                            Forms\Components\Section::make('Fotos del collage')
                                ->description('Las tres fotos que aparecen superpuestas a la derecha de la portada.')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\FileUpload::make('hero_image_1')
                                            ->label('Foto 1')
                                            ->image()
                                            ->directory('homepage')
                                            ->disk('public'),
                                        Forms\Components\FileUpload::make('hero_image_2')
                                            ->label('Foto 2')
                                            ->image()
                                            ->directory('homepage')
                                            ->disk('public'),
                                        Forms\Components\FileUpload::make('hero_image_3')
                                            ->label('Foto 3')
                                            ->image()
                                            ->directory('homepage')
                                            ->disk('public'),
                                    ]),
                                ]),
                        ]),

                    // ── CARACTERÍSTICAS ─────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Características')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Forms\Components\Section::make('Tarjeta 1')->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('feature_1_title')
                                        ->label('Título'),
                                    Forms\Components\FileUpload::make('feature_1_icon')
                                        ->label('Ícono')
                                        ->image()
                                        ->directory('homepage')
                                        ->disk('public'),
                                ]),
                                Forms\Components\Textarea::make('feature_1_description')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                            Forms\Components\Section::make('Tarjeta 2')->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('feature_2_title')
                                        ->label('Título'),
                                    Forms\Components\FileUpload::make('feature_2_icon')
                                        ->label('Ícono')
                                        ->image()
                                        ->directory('homepage')
                                        ->disk('public'),
                                ]),
                                Forms\Components\Textarea::make('feature_2_description')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                            Forms\Components\Section::make('Tarjeta 3')->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('feature_3_title')
                                        ->label('Título'),
                                    Forms\Components\FileUpload::make('feature_3_icon')
                                        ->label('Ícono')
                                        ->image()
                                        ->directory('homepage')
                                        ->disk('public'),
                                ]),
                                Forms\Components\Textarea::make('feature_3_description')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    // ── EQUIPO ──────────────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Sección Equipo')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\TextInput::make('team_title')
                                ->label('Título')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('team_description')
                                ->label('Descripción')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('team_button_text')
                                    ->label('Texto del botón'),
                                Forms\Components\TextInput::make('team_button_link')
                                    ->label('Link del botón'),
                            ]),
                            Forms\Components\FileUpload::make('team_image')
                                ->label('Foto del equipo')
                                ->image()
                                ->directory('homepage')
                                ->disk('public')
                                ->columnSpanFull(),
                        ]),

                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Título de Portada')
                    ->limit(60),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePages::route('/'),
            'create' => Pages\CreateHomePage::route('/create'),
            'edit' => Pages\EditHomePage::route('/{record}/edit'),
        ];
    }
}
