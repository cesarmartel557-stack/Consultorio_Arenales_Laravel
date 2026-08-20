<?php

namespace App\Livewire;

use App\Exceptions\SlotUnavailableException;
use App\Mail\AppointmentRequested;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingWizard extends Component
{
    public Doctor $doctor;

    public int $weekOffset = 0;

    public ?string $selectedDate = null;

    public ?string $selectedTime = null;

    public int $step = 1;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public ?string $health_insurance_id = null;

    public ?string $specialty_id = null;

    public string $notes = '';

    /** Honeypot: los bots completan campos ocultos, las personas no. */
    public string $website = '';

    public function mount(Doctor $doctor): void
    {
        $this->doctor = $doctor->load(['specialties', 'healthInsurances', 'schedules']);
        $this->specialty_id = (string) $this->doctor->specialties->first()?->id;
        $this->selectFirstAvailableDay();
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'phone' => ['required', 'string', 'min:6', 'max:20'],
            'health_insurance_id' => ['required', Rule::exists('health_insurances', 'id')],
            'specialty_id' => ['nullable', Rule::exists('specialties', 'id')],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'first_name.required' => 'Ingresá tu nombre',
            'last_name.required' => 'Ingresá tu apellido',
            'email.required' => 'Ingresá un mail válido',
            'email.email' => 'Ingresá un mail válido',
            'phone.required' => 'Ingresá un teléfono válido',
            'health_insurance_id.required' => 'Elegí una opción',
        ];
    }

    #[Computed]
    public function week(): \Illuminate\Support\Collection
    {
        return app(AvailabilityService::class)->week($this->doctor, $this->weekStart());
    }

    #[Computed]
    public function slots(): \Illuminate\Support\Collection
    {
        if (! $this->selectedDate) {
            return collect();
        }

        return app(AvailabilityService::class)
            ->slotsForDate($this->doctor, Carbon::parse($this->selectedDate));
    }

    public function previousWeek(): void
    {
        if ($this->weekOffset > 0) {
            $this->weekOffset--;
            $this->resetSelection();
            $this->selectFirstAvailableDay();
        }
    }

    public function nextWeek(): void
    {
        $this->weekOffset++;
        $this->resetSelection();
        $this->selectFirstAvailableDay();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = null;
    }

    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function goToDetails(): void
    {
        if (! $this->selectedDate || ! $this->selectedTime) {
            return;
        }

        $this->step = 2;
    }

    public function backToSchedule(): void
    {
        $this->step = 1;
    }

    public function submit(BookingService $booking)
    {
        if ($this->website !== '') {
            return null;
        }

        $this->validate();

        $key = 'booking:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Demasiadas solicitudes. Esperá unos minutos e intentá de nuevo.');

            return null;
        }

        $existing = $booking->existingRequestFor($this->doctor, $this->email);

        if ($existing) {
            $this->addError('form', sprintf(
                'Ya tenés una solicitud en curso con %s para el %s a las %s hs. Revisá tu mail para gestionarla.',
                $this->doctor->full_name,
                $existing->date->format('d/m/Y'),
                substr($existing->start_time, 0, 5),
            ));

            return null;
        }

        try {
            $appointment = $booking->book(
                $this->doctor,
                Carbon::parse($this->selectedDate),
                $this->selectedTime,
                [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'specialty_id' => $this->specialty_id ?: null,
                    'health_insurance_id' => $this->health_insurance_id,
                    'notes' => $this->notes ?: null,
                    'ip_address' => request()->ip(),
                ],
            );
        } catch (SlotUnavailableException $e) {
            $this->step = 1;
            $this->selectedTime = null;
            $this->addError('form', $e->getMessage());

            return null;
        }

        RateLimiter::hit($key, 900);

        Mail::to($appointment->email)->send(new AppointmentRequested($appointment));

        return $this->redirectRoute('turnos.gracias', ['appointment' => $appointment->uuid], navigate: true);
    }

    public function render()
    {
        return view('livewire.booking-wizard')
            ->layout('layouts.public', [
                'title' => 'Turnos | Consultorio Integral Arenales',
            ]);
    }

    private function weekStart(): Carbon
    {
        return now()->startOfWeek(Carbon::MONDAY)->addWeeks($this->weekOffset);
    }

    private function resetSelection(): void
    {
        $this->selectedDate = null;
        $this->selectedTime = null;
    }

    private function selectFirstAvailableDay(): void
    {
        $day = $this->week()->firstWhere('available_count', '>', 0)
            ?? $this->week()->firstWhere('has_schedule', true);

        $this->selectedDate = $day ? $day['date']->toDateString() : null;
    }
}
