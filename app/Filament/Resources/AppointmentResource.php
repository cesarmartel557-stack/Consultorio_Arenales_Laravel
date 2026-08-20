<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Services\AppointmentWorkflow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Turno';

    protected static ?string $pluralModelLabel = 'Turnos';

    protected static ?string $navigationLabel = 'Turnos';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // El médico sólo ve su propia agenda.
        $doctor = auth()->user()?->doctor;

        if ($doctor && ! auth()->user()->isAdmin()) {
            $query->where('doctor_id', $doctor->id);
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getEloquentQuery()
            ->where('status', AppointmentStatus::Pending)
            ->whereDate('date', '>=', today())
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Turno')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('doctor_id')
                        ->label('Profesional')
                        ->relationship('doctor', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('specialty_id')
                        ->label('Especialidad')
                        ->relationship('specialty', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Forms\Components\TimePicker::make('start_time')
                        ->label('Hora de inicio')
                        ->required()
                        ->seconds(false),
                    Forms\Components\TimePicker::make('end_time')
                        ->label('Hora de fin')
                        ->required()
                        ->seconds(false),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(AppointmentStatus::options())
                        ->required(),
                ]),

            Forms\Components\Section::make('Paciente')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(60),
                    Forms\Components\TextInput::make('last_name')->label('Apellido')->required()->maxLength(60),
                    Forms\Components\TextInput::make('email')->label('Email')->email()->required()->maxLength(120),
                    Forms\Components\TextInput::make('phone')->label('Teléfono')->tel()->required()->maxLength(20),
                    Forms\Components\Select::make('health_insurance_id')
                        ->label('Obra social')
                        ->relationship('healthInsurance', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->defaultGroup(
                Group::make('date')
                    ->label('Fecha')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Appointment $record) => ucfirst($record->date->translatedFormat('l d \d\e F')))
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Hora')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 5))
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Profesional')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => auth()->user()->isAdmin()),
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Paciente')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name']),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('healthInsurance.name')
                    ->label('Obra social')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state) => $state->label())
                    ->color(fn (AppointmentStatus $state) => $state->color()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(AppointmentStatus::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Profesional')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->isAdmin()),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde')->native(false)->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('until')->label('Hasta')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('date', '<=', $date))),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Sólo próximos')
                    ->query(fn (Builder $query) => $query->whereDate('date', '>=', today()))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Se le enviará un mail de confirmación al paciente.')
                    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                    ->action(fn (Appointment $record) => app(AppointmentWorkflow::class)->confirm($record)),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo (se incluye en el mail al paciente)')
                            ->rows(3),
                    ])
                    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                    ->action(fn (Appointment $record, array $data) => app(AppointmentWorkflow::class)->reject($record, $data['reason'] ?? null)),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo (se incluye en el mail al paciente)')
                            ->rows(3),
                    ])
                    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Confirmed && ! $record->isPast())
                    ->action(fn (Appointment $record, array $data) => app(AppointmentWorkflow::class)->cancel($record, $data['reason'] ?? null)),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('complete')
                        ->label('Marcar atendido')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Confirmed)
                        ->action(fn (Appointment $record) => $record->update(['status' => AppointmentStatus::Completed])),

                    Tables\Actions\Action::make('noShow')
                        ->label('No asistió')
                        ->icon('heroicon-o-user-minus')
                        ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Confirmed)
                        ->action(fn (Appointment $record) => $record->update(['status' => AppointmentStatus::NoShow])),

                    Tables\Actions\EditAction::make()->label('Editar'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('confirmMany')
                    ->label('Confirmar seleccionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function ($records) {
                        $workflow = app(AppointmentWorkflow::class);

                        $records
                            ->filter(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                            ->each(fn (Appointment $record) => $workflow->confirm($record));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
