<?php

namespace Tests\Feature;

use App\Livewire\BookingWizard;
use App\Models\Doctor;
use App\Models\HealthInsurance;
use App\Models\Specialty;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_service_week_executes_less_than_five_queries(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00'));

        $doctor = Doctor::factory()->create();

        for ($w = 1; $w <= 5; $w++) {
            $doctor->schedules()->create([
                'weekday' => $w,
                'start_time' => '08:00',
                'end_time' => '13:00',
                'slot_minutes' => 15,
                'is_active' => true,
            ]);
        }

        $doctor->scheduleExceptions()->create([
            'date' => '2026-09-02',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'reason' => 'Reunión',
        ]);

        $doctor->appointments()->create([
            'date' => '2026-09-01',
            'start_time' => '08:00',
            'end_time' => '08:15',
            'status' => 'confirmed',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '12345678',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $weekStart = Carbon::parse('2026-08-31');
        $week = app(AvailabilityService::class)->week($doctor, $weekStart);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $this->assertNotEmpty($week);
        $this->assertLessThan(5, $queryCount, "Se ejecutaron {$queryCount} consultas en la carga semanal.");
    }

    public function test_booking_wizard_livewire_component_executes_less_than_five_queries(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00'));

        $doctor = Doctor::factory()->create();
        $specialty = Specialty::create(['name' => 'Pediatría', 'slug' => 'pediatria']);
        $insurance = HealthInsurance::create(['name' => 'OSDE', 'slug' => 'osde']);
        $doctor->specialties()->attach($specialty->id);
        $doctor->healthInsurances()->attach($insurance->id);

        for ($w = 1; $w <= 5; $w++) {
            $doctor->schedules()->create([
                'weekday' => $w,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'slot_minutes' => 15,
                'is_active' => true,
            ]);
        }

        $doctor->appointments()->create([
            'date' => '2026-09-01',
            'start_time' => '08:00',
            'end_time' => '08:15',
            'status' => 'confirmed',
            'first_name' => 'Paciente',
            'last_name' => 'Prueba',
            'email' => 'paciente@example.com',
            'phone' => '12345678',
        ]);

        $doctor->load(['specialties', 'healthInsurances', 'schedules']);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::test(BookingWizard::class, ['doctor' => $doctor])
            ->assertSee($doctor->full_name)
            ->assertSee('Pediatría');

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $this->assertLessThan(5, $queryCount, "El componente Livewire ejecutó {$queryCount} consultas en total.");
    }
}
