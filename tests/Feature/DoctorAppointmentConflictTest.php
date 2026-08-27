<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\HealthInsurance;
use App\Models\Specialty;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DoctorAppointmentConflictTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private HealthInsurance $insurance;

    private Specialty $specialty;

    private Carbon $nextMonday;

    private AvailabilityService $availabilityService;

    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-01 08:00:00'));

        $this->availabilityService = app(AvailabilityService::class);
        $this->bookingService = app(BookingService::class);

        $this->doctor = Doctor::create([
            'title' => 'Dr.',
            'name' => 'Carlos Bianchi',
            'slug' => 'carlos-bianchi',
            'headline' => 'Cardiología clínica',
            'slot_minutes' => 30,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'is_active' => true,
        ]);

        $this->specialty = Specialty::create([
            'name' => 'Cardiología',
            'slug' => 'cardiologia',
        ]);
        $this->doctor->specialties()->attach($this->specialty->id);

        $this->insurance = HealthInsurance::create([
            'name' => 'Swiss Medical',
            'slug' => 'swiss-medical',
        ]);
        $this->doctor->healthInsurances()->attach($this->insurance->id);

        // Lunes 7 de Septiembre de 2026
        $this->nextMonday = Carbon::parse('2026-09-07');

        // Configuración de horario de atención los días Lunes de 09:00 a 11:00
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

    public function test_un_medico_no_puede_recibir_dos_citas_en_el_mismo_horario(): void
    {
        // 1. Agendamos la primera cita para las 09:30
        $this->bookAppointment($this->doctor, $this->nextMonday, '09:30', 'paciente1@test.com', '11111111');

        // 2. Verificamos que el slot 09:30 ya no figure como disponible
        $this->assertFalse(
            $this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:30')
        );

        // 3. Otro horario como las 10:00 sigue estando disponible
        $this->assertTrue(
            $this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '10:00')
        );

        // 4. Intentar agendar una segunda cita en el mismo horario debe arrojar SlotUnavailableException
        $this->expectException(SlotUnavailableException::class);
        $this->bookAppointment($this->doctor, $this->nextMonday, '09:30', 'paciente2@test.com', '22222222');
    }

    public function test_la_base_de_datos_rechaza_citas_duplicadas_en_el_mismo_horario_para_un_mismo_medico(): void
    {
        // Agendamos primera cita
        $this->bookAppointment($this->doctor, $this->nextMonday, '09:30');

        // Intentamos insertar directamente en la BD salteando el servicio para verificar la restricción de active_slot
        $this->expectException(UniqueConstraintViolationException::class);

        Appointment::create([
            'doctor_id' => $this->doctor->id,
            'specialty_id' => $this->specialty->id,
            'health_insurance_id' => $this->insurance->id,
            'date' => $this->nextMonday->toDateString(),
            'start_time' => '09:30',
            'end_time' => '10:00',
            'status' => AppointmentStatus::Pending,
            'first_name' => 'Segundo',
            'last_name' => 'Paciente',
            'email' => 'segundo@test.com',
            'phone' => '1199887766',
            'dni' => '33333333',
        ]);
    }

    public function test_si_una_cita_se_cancela_se_libera_el_horario_y_permite_recibir_una_nueva_cita(): void
    {
        // Agendamos la cita a las 09:30
        $appointment = $this->bookAppointment($this->doctor, $this->nextMonday, '09:30', 'paciente1@test.com', '11111111');
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:30'));

        // Cancelamos la cita
        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_by' => 'patient',
            'cancelled_at' => now(),
        ]);

        // El horario debe estar nuevamente disponible
        $this->assertTrue($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:30'));

        // Se puede agendar exitosamente para otro paciente
        $newAppointment = $this->bookAppointment($this->doctor, $this->nextMonday, '09:30', 'paciente2@test.com', '22222222');
        $this->assertInstanceOf(Appointment::class, $newAppointment);
        $this->assertSame('paciente2@test.com', $newAppointment->email);
        $this->assertSame(AppointmentStatus::Pending, $newAppointment->status);
    }

    public function test_no_se_pueden_recibir_citas_fuera_de_los_horarios_configurados_en_doctor_schedules(): void
    {
        // Horario fuera de la franja configurada (médico atiende de 09:00 a 11:00, probamos a las 15:00)
        $this->assertFalse(
            $this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '15:00')
        );

        // Día sin horarios de atención (ej: Domingo 6 de Septiembre de 2026)
        $sunday = Carbon::parse('2026-09-06');
        $this->assertFalse(
            $this->availabilityService->isSlotAvailable($this->doctor, $sunday, '09:00')
        );

        // Intentar reservar en día u horario no configurado debe lanzar SlotUnavailableException
        $this->expectException(SlotUnavailableException::class);
        $this->bookAppointment($this->doctor, $this->nextMonday, '15:00');
    }

    public function test_no_se_pueden_recibir_citas_en_dias_con_schedule_exception_de_dia_completo(): void
    {
        // Se define una excepción de día completo para el médico (ej: Congreso / Licencia)
        $this->doctor->scheduleExceptions()->create([
            'date' => $this->nextMonday->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'reason' => 'Congreso Médico Anual',
        ]);

        // La lista de turnos para esa fecha debe ser vacía
        $slots = $this->availabilityService->slotsForDate($this->doctor, $this->nextMonday);
        $this->assertTrue($slots->isEmpty());

        // Ningún horario debe figurar como disponible
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:00'));
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:30'));
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '10:00'));

        // Intentar reservar en ese día lanza SlotUnavailableException
        $this->expectException(SlotUnavailableException::class);
        $this->bookAppointment($this->doctor, $this->nextMonday, '09:00');
    }

    public function test_no_se_pueden_recibir_citas_en_el_rango_bloqueado_por_schedule_exception_parcial(): void
    {
        // Se define una excepción parcial de 10:00 a 11:00
        $this->doctor->scheduleExceptions()->create([
            'date' => $this->nextMonday->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'reason' => 'Reunión de departamento',
        ]);

        // Los turnos en el rango de la excepción no deben estar disponibles
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '10:00'));
        $this->assertFalse($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '10:30'));

        // Los turnos fuera del rango de la excepción deben seguir disponibles
        $this->assertTrue($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:00'));
        $this->assertTrue($this->availabilityService->isSlotAvailable($this->doctor, $this->nextMonday, '09:30'));

        // Se puede agendar en el horario no afectado
        $appointment = $this->bookAppointment($this->doctor, $this->nextMonday, '09:00');
        $this->assertInstanceOf(Appointment::class, $appointment);

        // Intentar reservar dentro del rango de la excepción debe lanzar SlotUnavailableException
        $this->expectException(SlotUnavailableException::class);
        $this->bookAppointment($this->doctor, $this->nextMonday, '10:00');
    }

    public function test_dos_medicos_diferentes_pueden_recibir_citas_en_el_mismo_horario_sin_conflicto(): void
    {
        // Creamos un segundo médico con su propio horario los Lunes
        $secondDoctor = Doctor::create([
            'title' => 'Dra.',
            'name' => 'Maria Fernandez',
            'slug' => 'maria-fernandez',
            'headline' => 'Dermatología',
            'slot_minutes' => 30,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'is_active' => true,
        ]);
        $secondDoctor->specialties()->attach($this->specialty->id);
        $secondDoctor->healthInsurances()->attach($this->insurance->id);

        $secondDoctor->schedules()->create([
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'is_active' => true,
        ]);

        // Reservamos a las 09:30 para el primer médico
        $app1 = $this->bookAppointment($this->doctor, $this->nextMonday, '09:30', 'paciente1@test.com', '11111111');

        // Verificamos que el segundo médico sigue teniendo libre las 09:30
        $this->assertTrue($this->availabilityService->isSlotAvailable($secondDoctor, $this->nextMonday, '09:30'));

        // Reservamos a las 09:30 para el segundo médico sin ningún conflicto
        $app2 = $this->bookAppointment($secondDoctor, $this->nextMonday, '09:30', 'paciente2@test.com', '22222222');

        $this->assertNotSame($app1->doctor_id, $app2->doctor_id);
        $this->assertSame('09:30', $app1->start_time);
        $this->assertSame('09:30', $app2->start_time);
        $this->assertSame($this->nextMonday->toDateString(), $app1->date->toDateString());
        $this->assertSame($this->nextMonday->toDateString(), $app2->date->toDateString());
    }

    public function test_la_relacion_appointments_en_specialty_retorna_sus_turnos(): void
    {
        $appointment = $this->bookAppointment($this->doctor, $this->nextMonday, '09:30');

        $this->assertTrue($this->specialty->appointments->contains($appointment));
        $this->assertSame(1, $this->specialty->appointments()->count());
    }

    public function test_la_generacion_semanal_por_lotes_de_availability_service_calcula_correctamente_los_dias(): void
    {
        // 4 slots de 30 mins: 09:00, 09:30, 10:00, 10:30
        $this->bookAppointment($this->doctor, $this->nextMonday, '09:30');

        $week = $this->availabilityService->week($this->doctor, $this->nextMonday);

        // Debe retornar 6 días (lunes a sábado)
        $this->assertCount(6, $week);

        // Lunes (offset 0): tiene agenda y 3 turnos disponibles (4 slots - 1 ocupado)
        $monday = $week->first();
        $this->assertTrue($monday['has_schedule']);
        $this->assertSame(3, $monday['available_count']);

        // Martes (offset 1): no tiene agenda
        $tuesday = $week->get(1);
        $this->assertFalse($tuesday['has_schedule']);
        $this->assertSame(0, $tuesday['available_count']);
    }

    public function test_gabriel_toledo_tiene_turnos_disponibles_en_septiembre_excepto_el_4_de_septiembre(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-26 09:00:00'));

        $doctorToledo = Doctor::create([
            'title' => 'Dr.',
            'name' => 'Gabriel Toledo',
            'slug' => 'gabriel-toledo',
            'headline' => 'Especialista en Ginecología, Obstetricia y Patología mamaria',
            'slot_minutes' => 15,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'is_active' => true,
        ]);

        // Horarios habituales: Martes (2) 14:00-19:00, Jueves (4) 14:00-19:00, Viernes (5) 09:00-13:00
        $doctorToledo->schedules()->createMany([
            ['weekday' => 2, 'start_time' => '14:00', 'end_time' => '19:00', 'is_active' => true],
            ['weekday' => 4, 'start_time' => '14:00', 'end_time' => '19:00', 'is_active' => true],
            ['weekday' => 5, 'start_time' => '09:00', 'end_time' => '13:00', 'is_active' => true],
        ]);

        // Excepción únicamente el 4 de septiembre de 2026 (día completo)
        $doctorToledo->scheduleExceptions()->create([
            'date' => '2026-09-04',
            'start_time' => null,
            'end_time' => null,
            'reason' => 'Día no laborable / Licencia',
        ]);

        // 1. Martes 1 de Septiembre (día laborable): debe tener slots disponibles
        $tuesday1 = Carbon::parse('2026-09-01');
        $slotsTuesday1 = $this->availabilityService->slotsForDate($doctorToledo, $tuesday1);
        $this->assertNotEmpty($slotsTuesday1);
        $this->assertTrue($this->availabilityService->isSlotAvailable($doctorToledo, $tuesday1, '14:00'));

        // 2. Jueves 3 de Septiembre (día laborable): debe tener slots disponibles
        $thursday3 = Carbon::parse('2026-09-03');
        $slotsThursday3 = $this->availabilityService->slotsForDate($doctorToledo, $thursday3);
        $this->assertNotEmpty($slotsThursday3);
        $this->assertTrue($this->availabilityService->isSlotAvailable($doctorToledo, $thursday3, '14:00'));

        // 3. Viernes 4 de Septiembre (día de la excepción): NO debe tener ningún slot disponible
        $friday4 = Carbon::parse('2026-09-04');
        $slotsFriday4 = $this->availabilityService->slotsForDate($doctorToledo, $friday4);
        $this->assertTrue($slotsFriday4->isEmpty());
        $this->assertFalse($this->availabilityService->isSlotAvailable($doctorToledo, $friday4, '09:00'));

        // 4. Martes 8 de Septiembre (semana siguiente): debe tener slots disponibles
        $tuesday8 = Carbon::parse('2026-09-08');
        $slotsTuesday8 = $this->availabilityService->slotsForDate($doctorToledo, $tuesday8);
        $this->assertNotEmpty($slotsTuesday8);
        $this->assertTrue($this->availabilityService->isSlotAvailable($doctorToledo, $tuesday8, '14:00'));

        // 5. Martes 15 de Septiembre: debe tener slots disponibles
        $tuesday15 = Carbon::parse('2026-09-15');
        $slotsTuesday15 = $this->availabilityService->slotsForDate($doctorToledo, $tuesday15);
        $this->assertNotEmpty($slotsTuesday15);
        $this->assertTrue($this->availabilityService->isSlotAvailable($doctorToledo, $tuesday15, '14:00'));
    }

    private function bookAppointment(
        Doctor $doctor,
        Carbon $date,
        string $time,
        string $email = 'paciente@test.com',
        string $dni = '12345678'
    ): Appointment {
        return $this->bookingService->book($doctor, $date, $time, [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => $email,
            'phone' => '1122334455',
            'dni' => $dni,
            'specialty_id' => $this->specialty->id,
            'health_insurance_id' => $this->insurance->id,
        ]);
    }
}
