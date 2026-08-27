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
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

        $this->expectException(UniqueConstraintViolationException::class);

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
            ->set('dni', '12345678')
            ->set('health_insurance_id', (string) $this->insurance->id)
            ->call('submit')
            ->assertHasNoErrors();

        $appointment = Appointment::first();

        $this->assertSame('ana@test.com', $appointment->email);
        $this->assertSame('12345678', $appointment->dni);
        $this->assertSame('10:00', substr($appointment->start_time, 0, 5));
        $this->assertSame('10:30', substr($appointment->end_time, 0, 5));

        Mail::assertSent(AppointmentRequested::class);
    }

    public function test_el_wizard_permite_reservar_como_particular(): void
    {
        Mail::fake();

        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->assertSet('step', 2)
            ->set('first_name', 'Cesar')
            ->set('last_name', 'Martel')
            ->set('email', 'cesar@test.com')
            ->set('phone', '1155667788')
            ->set('dni', '87654321')
            ->set('health_insurance_id', 'particular')
            ->call('submit')
            ->assertHasNoErrors();

        $appointment = Appointment::first();

        $this->assertNotNull($appointment);
        $this->assertSame('Cesar', $appointment->first_name);
        $this->assertSame('Martel', $appointment->last_name);
        $this->assertNull($appointment->health_insurance_id);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);

        Mail::assertSent(AppointmentRequested::class);
    }

    public function test_el_wizard_valida_los_datos_obligatorios(): void
    {
        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->call('submit')
            ->assertHasErrors(['first_name', 'last_name', 'email', 'phone', 'dni', 'health_insurance_id']);

        $this->assertSame(0, Appointment::count());
    }

    public function test_detecta_una_solicitud_duplicada_por_dni(): void
    {
        // Mismo DNI, distinto email -> debe bloquear de todas formas
        $this->bookSlot('09:30', 'original@test.com', '99887766');

        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->set('selectedDate', $this->nextMonday->toDateString())
            ->call('selectTime', '10:00')
            ->call('goToDetails')
            ->set('first_name', 'Ana')
            ->set('last_name', 'Pérez')
            ->set('email', 'otro@test.com')
            ->set('phone', '1155667788')
            ->set('dni', '99887766')
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

        $url = URL::signedRoute('turnos.cancelar', $appointment);

        $this->post($url, ['reason' => 'No puedo asistir'])->assertRedirect();

        $this->assertSame(AppointmentStatus::Cancelled, $appointment->fresh()->status);
        $this->assertSame('patient', $appointment->fresh()->cancelled_by);
    }

    public function test_rechaza_la_gestion_sin_firma_valida(): void
    {
        $appointment = $this->bookSlot('09:30');

        $this->get(route('turnos.gestion', $appointment))->assertForbidden();
    }

    public function test_el_wizard_muestra_el_titulo_y_respeta_saltos_de_linea_de_la_biografia(): void
    {
        $this->doctor->update([
            'headline' => 'Especialista en Fertilidad y Reproducción',
            'bio' => "Solo atención virtual.\nConsultas al 11 5132 9844",
        ]);

        Livewire::test(BookingWizard::class, ['doctor' => $this->doctor])
            ->assertSee('Especialista en Fertilidad y Reproducción')
            ->assertSeeHtml('Solo atención virtual.<br />')
            ->assertSeeHtml('Consultas al 11 5132 9844');
    }

    private function bookSlot(string $time, string $email = 'paciente@test.com', string $dni = '12345678'): Appointment
    {
        return app(BookingService::class)->book($this->doctor, $this->nextMonday, $time, [
            'first_name' => 'Paciente',
            'last_name' => 'Test',
            'email' => $email,
            'phone' => '1122334455',
            'dni' => $dni,
            'health_insurance_id' => $this->insurance->id,
        ]);
    }
}
