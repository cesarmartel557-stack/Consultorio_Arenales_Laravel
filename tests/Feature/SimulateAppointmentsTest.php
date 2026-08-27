<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentRequested;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\HealthInsurance;
use App\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SimulateAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00')); // Martes
    }

    public function test_el_comando_crea_turnos_simulados_correctamente(): void
    {
        Mail::fake();

        $doctor = Doctor::create([
            'title' => 'Dr.',
            'name' => 'Gonzalo Méndez',
            'slug' => 'gonzalo-mendez',
            'headline' => 'Especialista en Ginecología',
            'slot_minutes' => 30,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'is_active' => true,
        ]);

        $specialty = Specialty::create(['name' => 'Ginecología', 'slug' => 'ginecologia']);
        $doctor->specialties()->attach($specialty->id);

        $insurance = HealthInsurance::create(['name' => 'OSDE', 'slug' => 'osde']);
        $doctor->healthInsurances()->attach($insurance->id);

        // Martes (2) de 10:00 a 14:00
        $doctor->schedules()->create([
            'weekday' => 2,
            'start_time' => '10:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        $this->artisan('appointments:simulate', ['--count' => 3])
            ->expectsOutputToContain('Se crearon exitosamente 3 turnos de prueba')
            ->assertSuccessful();

        $this->assertCount(3, Appointment::all());

        $appointment = Appointment::first();
        $this->assertSame($doctor->id, $appointment->doctor_id);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertNotNull($appointment->first_name);
        $this->assertNotNull($appointment->dni);
        $this->assertNotNull($appointment->email);

        Mail::assertSent(AppointmentRequested::class, 3);
    }

    public function test_el_comando_falla_si_no_hay_medicos_activos(): void
    {
        $this->artisan('appointments:simulate')
            ->expectsOutputToContain('No hay profesionales activos')
            ->assertFailed();
    }
}
