<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Livewire\BookingWizard;
use App\Mail\AppointmentRequested;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\HealthInsurance;
use App\Models\Specialty;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private HealthInsurance $insurance;

    private Carbon $nextMonday;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00'));

        $this->doctor = Doctor::create([
            'title' => 'Dr.',
            'name' => 'Test Médico',
            'slug' => 'test-medico',
            'headline' => 'Especialista de prueba',
            'slot_minutes' => 30,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'is_active' => true,
        ]);

        $this->doctor->specialties()->attach(
            Specialty::create(['name' => 'Ginecología', 'slug' => 'ginecologia'])->id
        );

        $this->insurance = HealthInsurance::create(['name' => 'OSDE', 'slug' => 'osde']);
        $this->doctor->healthInsurances()->attach($this->insurance->id);

        $this->nextMonday = Carbon::parse('2026-09-07');

        $this->doctor->schedules()->create([
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_genera_los_slots_segun_la_agenda_del_medico(): void
    {
        $slots = app(AvailabilityService::class)->slotsForDate($this->doctor, $this->nextMonday);

        $this->assertSame(['09:00', '09:30', '10:00', '10:30'], $slots->pluck('time')->all());
        $this->assertTrue($slots->every(fn ($slot) => $slot['available']));
    }

    public function test_un_turno_pendiente_bloquea_el_horario_para_otros_pacientes(): void
    {
        $this->bookSlot('09:30');

        $slots = app(AvailabilityService::class)->slotsForDate($this->doctor, $this->nextMonday);

        $this->assertFalse($slots->firstWhere('time', '09:30')['available']);
        $this->assertTrue($slots->firstWhere('time', '10:00')['available']);
    }

    public function test_cancelar_libera_el_horario(): void
    {
        $appointment = $this->bookSlot('09:30');

        $appointment->update(['status' => AppointmentStatus::Cancelled]);

        $slots = app(AvailabilityService::class)->slotsForDate($this->doctor, $this->nextMonday);

        $this->assertTrue($slots->firstWhere('time', '09:30')['available']);
    }

    public function test_no_permite_reservar_dos_veces_el_mismo_horario(): void
    {
        $this->bookSlot('09:30');

        $this->expectException(SlotUnavailableException::class);

        $this->bookSlot('09:30', 'otro@paciente.com');
    }

    public function test_la_base_de_datos_rechaza_el_slot_duplicado(): void
    {
        $this->bookSlot('09:30');

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        // Salteamos la validación del servicio para probar el candado de la base.
        Appointment::create([
            'doctor_id' => $this->doctor->id,
            'date' => $this->nextMonday->toDateString(),
            'start_time' => '09:30',
            'end_time' => '10:00',
            'status' => AppointmentStatus::Pending,
            'first_name' => 'Race',
            'last_name' => 'Condition',
            'email' => 'race@test.com',
            'phone' => '1122334455',
        ]);
    }

    public function test_el_wizard_registra_el_turno_y_envia_el_mail(): void
    {
        Mail::fake();

        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->assertSet('step', 2)
            ->set('first_name', 'Ana')
            ->set('last_name', 'Pérez')
            ->set('email', 'ana@test.com')
            ->set('phone', '1155667788')
            ->set('health_insurance_id', (string) $this->insurance->id)
            ->call('submit')
            ->assertHasNoErrors();

        $appointment = Appointment::first();

        $this->assertSame('ana@test.com', $appointment->email);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame('10:00:00', $appointment->start_time);
        $this->assertSame('10:30:00', $appointment->end_time);

        Mail::assertSent(AppointmentRequested::class);
    }

    public function test_el_wizard_valida_los_datos_obligatorios(): void
    {
        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->call('submit')
            ->assertHasErrors(['first_name', 'last_name', 'email', 'phone', 'health_insurance_id']);

        $this->assertSame(0, Appointment::count());
    }

    public function test_detecta_una_solicitud_duplicada_del_mismo_email(): void
    {
        $this->bookSlot('09:30', 'repetido@test.com');

        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->set('first_name', 'Ana')
            ->set('last_name', 'Pérez')
            ->set('email', 'repetido@test.com')
            ->set('phone', '1155667788')
            ->set('health_insurance_id', (string) $this->insurance->id)
            ->call('submit')
            ->assertHasErrors('form');

        $this->assertSame(1, Appointment::count());
    }

    public function test_el_honeypot_descarta_los_bots(): void
    {
        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->set('website', 'http://spam.example')
            ->call('submit');

        $this->assertSame(0, Appointment::count());
    }

    public function test_el_paciente_cancela_con_url_firmada(): void
    {
        Mail::fake();
        $appointment = $this->bookSlot('09:30');

        $url = \Illuminate\Support\Facades\URL::signedRoute('turnos.cancelar', $appointment);

        $this->post($url, ['reason' => 'No puedo asistir'])->assertRedirect();

        $this->assertSame(AppointmentStatus::Cancelled, $appointment->fresh()->status);
        $this->assertSame('patient', $appointment->fresh()->cancelled_by);
    }

    public function test_rechaza_la_gestion_sin_firma_valida(): void
    {
        $appointment = $this->bookSlot('09:30');

        $this->get(route('turnos.gestion', $appointment))->assertForbidden();
    }

    private function bookSlot(string $time, string $email = 'paciente@test.com'): Appointment
    {
        return app(BookingService::class)->book($this->doctor, $this->nextMonday, $time, [
            'first_name' => 'Paciente',
            'last_name' => 'Test',
            'email' => $email,
            'phone' => '1122334455',
            'health_insurance_id' => $this->insurance->id,
        ]);
    }
}
