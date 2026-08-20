<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HealthInsuranceResource\Pages;
use App\Models\HealthInsurance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class HealthInsuranceResource extends Resource
{
    protected static ?string $model = HealthInsurance::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'Obra social';

    protected static ?string $pluralModelLabel = 'Obras sociales';

    protected static ?string $navigationLabel = 'Obras sociales';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
            Forms\Components\TextInput::make('slug')
                ->label('URL')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(140),
            Forms\Components\FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->directory('insurances')
                ->disk('public'),
            Forms\Components\Toggle::make('is_active')
                ->label('Activa')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('')->disk('public'),
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('doctors_count')->label('Profesionales')->counts('doctors'),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHealthInsurances::route('/'),
            'create' => Pages\CreateHealthInsurance::route('/create'),
            'edit' => Pages\EditHealthInsurance::route('/{record}/edit'),
        ];
    }
}
