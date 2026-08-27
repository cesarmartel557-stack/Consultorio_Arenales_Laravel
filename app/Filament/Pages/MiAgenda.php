<?php

namespace App\Filament\Pages;

use App\Models\DoctorSchedule;
use App\Models\HealthInsurance;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MiAgenda extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Mi agenda';

    protected static ?string $title = 'Mi agenda';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.mi-agenda';

    /** Datos del formulario enlazados con Livewire */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        // Solo médicos con ficha vinculada ven esta página.
        return auth()->user()?->doctor !== null;
    }

    public function mount(): void
    {
        $doctor = auth()->user()->doctor->load(['schedules', 'scheduleExceptions', 'healthInsurances']);

        $this->form->fill([
            'slot_minutes' => $doctor->slot_minutes,
            'min_hours_notice' => $doctor->min_hours_notice,
            'healthInsurances' => $doctor->healthInsurances->pluck('id')->toArray(),
            'schedules' => $doctor->schedules->map(fn ($s) => [
                'id' => $s->id,
                'weekday' => $s->weekday,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'slot_minutes' => $s->slot_minutes,
                'is_active' => $s->is_active,
            ])->toArray(),
            'scheduleExceptions' => $doctor->scheduleExceptions->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->date?->toDateString(),
                'start_time' => $e->start_time,
                'end_time' => $e->end_time,
                'reason' => $e->reason,
            ])->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Obras Sociales')
                    ->description('Seleccioná las obras sociales por las que atendés.')
                    ->schema([
                        Forms\Components\CheckboxList::make('healthInsurances')
                            ->label('Obras Sociales')
                            ->options(HealthInsurance::where('is_active', true)->where('name', '!=', 'Particular')->orderBy('sort_order')->pluck('name', 'id'))
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Días y horarios de atención')
                    ->description('Configurá los días y rangos horarios en los que atendés.')
                    ->schema([
                        Forms\Components\Repeater::make('schedules')
                            ->label('Días de atención')
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar día')
                            ->schema([
                                Forms\Components\Select::make('weekday')
                                    ->label('Día')
                                    ->options(DoctorSchedule::WEEKDAYS)
                                    ->required(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Desde')
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i')
                                    ->required(),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Hasta')
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i')
                                    ->required()
                                    ->after('start_time'),
                                Forms\Components\TextInput::make('slot_minutes')
                                    ->label('Min. por turno')
                                    ->numeric()
                                    ->minValue(5)
                                    ->maxValue(120)
                                    ->placeholder('Por defecto'),
                            ]),
                    ]),

                Forms\Components\Section::make('Excepciones (vacaciones, feriados, bloqueos)')
                    ->description('Bloqueá días completos o rangos horarios específicos.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Repeater::make('scheduleExceptions')
                            ->label('Excepciones')
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar excepción')
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->label('Fecha')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->required(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Desde')
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i')
                                    ->helperText('Vacío = todo el día'),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Hasta')
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i'),
                                Forms\Components\TextInput::make('reason')
                                    ->label('Motivo')
                                    ->maxLength(120),
                            ]),
                    ]),

                Forms\Components\Section::make('Reglas de reserva')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('slot_minutes')
                            ->label('Duración del turno (min)')
                            ->numeric()
                            ->default(15)
                            ->minValue(5)
                            ->maxValue(120)
                            ->required()
                            ->helperText('Duración por defecto si el día no tiene una duración específica.'),
                        Forms\Components\TextInput::make('min_hours_notice')
                            ->label('Anticipación mínima (hs)')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->required()
                            ->helperText('Horas mínimas de anticipación requeridas para solicitar un turno.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $doctor = auth()->user()->doctor;
        $state = $this->form->getState();

        // Actualizar reglas de reserva en el modelo doctor
        $doctor->update([
            'slot_minutes' => $state['slot_minutes'] ?? 15,
            'min_hours_notice' => $state['min_hours_notice'] ?? 2,
        ]);

        // Sincronizar obras sociales
        $doctor->healthInsurances()->sync($state['healthInsurances'] ?? []);

        // Sincronizar schedules: eliminar los que ya no existen y crear/actualizar
        $this->syncRepeater(
            $doctor,
            'schedules',
            $state['schedules'] ?? [],
            ['weekday', 'start_time', 'end_time', 'slot_minutes', 'is_active'],
        );

        // Sincronizar excepciones
        $this->syncRepeater(
            $doctor,
            'scheduleExceptions',
            $state['scheduleExceptions'] ?? [],
            ['date', 'start_time', 'end_time', 'reason'],
        );

        Notification::make()
            ->title('Agenda guardada')
            ->body('Los cambios se aplicarán a los nuevos turnos.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->submit('save'),
        ];
    }

    /**
     * Sincroniza los registros de un Repeater:
     * - Actualiza los que tienen id
     * - Crea los nuevos
     * - Elimina los que ya no están en el array
     */
    private function syncRepeater($doctor, string $relation, array $items, array $fillable): void
    {
        $incomingIds = collect($items)->pluck('id')->filter()->all();

        // Eliminar los que no llegaron
        $doctor->{$relation}()->whereNotIn('id', $incomingIds)->delete();

        foreach ($items as $item) {
            $data = collect($item)->only($fillable)->toArray();

            if (! empty($item['id'])) {
                $doctor->{$relation}()->where('id', $item['id'])->update($data);
            } else {
                $doctor->{$relation}()->create($data);
            }
        }
    }
}
