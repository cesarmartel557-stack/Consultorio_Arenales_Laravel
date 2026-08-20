<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Profesional';

    protected static ?string $pluralModelLabel = 'Profesionales';

    protected static ?string $navigationLabel = 'Profesionales';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del profesional')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Tratamiento')
                        ->placeholder('Dr. / Dra. / Lic.')
                        ->maxLength(20),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre y apellido')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                    Forms\Components\TextInput::make('slug')
                        ->label('URL')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(140),
                    Forms\Components\TextInput::make('license')
                        ->label('Matrícula')
                        ->placeholder('MN 12345 | MP 67890')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('headline')
                        ->label('Especialidad (texto que se muestra)')
                        ->maxLength(180)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('photo')
                        ->label('Foto')
                        ->image()
                        ->directory('doctors')
                        ->disk('public')
                        ->imageEditor(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo (visible en el sitio)')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Clasificación')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('specialties')
                        ->label('Especialidades')
                        ->relationship('specialties', 'name')
                        ->multiple()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('healthInsurances')
                        ->label('Obras sociales')
                        ->relationship('healthInsurances', 'name')
                        ->multiple()
                        ->preload(),
                ]),

            Forms\Components\Section::make('Agenda')
                ->description('Días y horarios en los que atiende. Los turnos disponibles se calculan a partir de esto.')
                ->schema([
                    Forms\Components\Repeater::make('schedules')
                        ->label('Días de atención')
                        ->relationship()
                        ->columns(4)
                        ->defaultItems(1)
                        ->schema([
                            Forms\Components\Select::make('weekday')
                                ->label('Día')
                                ->options(DoctorSchedule::WEEKDAYS)
                                ->required(),
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Desde')
                                ->seconds(false)
                                ->required(),
                            Forms\Components\TimePicker::make('end_time')
                                ->label('Hasta')
                                ->seconds(false)
                                ->required()
                                ->after('start_time'),
                            Forms\Components\TextInput::make('slot_minutes')
                                ->label('Min. por turno')
                                ->numeric()
                                ->minValue(5)
                                ->maxValue(120)
                                ->placeholder('Por defecto'),
                        ]),

                    Forms\Components\Repeater::make('scheduleExceptions')
                        ->label('Excepciones (vacaciones, feriados, bloqueos)')
                        ->relationship()
                        ->columns(4)
                        ->defaultItems(0)
                        ->collapsed()
                        ->schema([
                            Forms\Components\DatePicker::make('date')
                                ->label('Fecha')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->required(),
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Desde')
                                ->seconds(false)
                                ->helperText('Vacío = todo el día'),
                            Forms\Components\TimePicker::make('end_time')
                                ->label('Hasta')
                                ->seconds(false),
                            Forms\Components\TextInput::make('reason')
                                ->label('Motivo')
                                ->maxLength(120),
                        ]),
                ]),

            Forms\Components\Section::make('Reglas de reserva')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('slot_minutes')
                        ->label('Duración del turno (min)')
                        ->numeric()
                        ->default(15)
                        ->minValue(5)
                        ->maxValue(120)
                        ->required(),
                    Forms\Components\TextInput::make('min_hours_notice')
                        ->label('Anticipación mínima (hs)')
                        ->numeric()
                        ->default(2)
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('max_days_ahead')
                        ->label('Reservar hasta (días)')
                        ->numeric()
                        ->default(60)
                        ->minValue(1)
                        ->required(),
                ]),

            Forms\Components\Section::make('Acceso al panel')
                ->description('Opcional: creá un usuario para que el profesional gestione sus propios turnos.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Usuario vinculado')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                            Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique('users', 'email'),
                            Forms\Components\TextInput::make('password')
                                ->label('Contraseña')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->dehydrateStateUsing(fn (string $state) => Hash::make($state)),
                        ])
                        ->createOptionUsing(fn (array $data) => \App\Models\User::create([
                            ...$data,
                            'role' => UserRole::Doctor,
                        ])->id),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->disk('public'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Profesional')
                    ->searchable(['name'])
                    ->sortable(['name']),
                Tables\Columns\TextColumn::make('specialties.name')
                    ->label('Especialidades')
                    ->badge(),
                Tables\Columns\TextColumn::make('license')
                    ->label('Matrícula')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('schedules_count')
                    ->label('Días')
                    ->counts('schedules'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('specialties')
                    ->label('Especialidad')
                    ->relationship('specialties', 'name')
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
