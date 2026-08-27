<?php

namespace App\Console\Commands;

use App\Mail\AppointmentRequested;
use App\Models\Doctor;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SimulateAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:simulate {--count=5 : Cantidad de turnos a simular}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula reservas de turnos reales en slots disponibles usando AvailabilityService y BookingService';

    /**
     * Execute the console command.
     */
    public function handle(AvailabilityService $availabilityService, BookingService $bookingService): int
    {
        $count = max(1, (int) $this->option('count'));

        $this->info("Buscando slots disponibles en los próximos 7 días para simular {$count} turnos...");

        $doctors = Doctor::active()
            ->with(['specialties', 'healthInsurances', 'schedules', 'scheduleExceptions'])
            ->get();

        if ($doctors->isEmpty()) {
            $this->error('No hay profesionales activos en el sistema.');

            return self::FAILURE;
        }

        // Recolectar todos los slots disponibles reales en los próximos 7 días
        $candidateSlots = collect();
        $startDate = today();
        $endDate = today()->addDays(7);

        foreach ($doctors as $doctor) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $slots = $availabilityService->slotsForDate($doctor, $date);

                foreach ($slots as $slot) {
                    if (! empty($slot['available'])) {
                        $candidateSlots->push([
                            'doctor' => $doctor,
                            'date' => $date->copy(),
                            'time' => $slot['time'],
                        ]);
                    }
                }
            }
        }

        if ($candidateSlots->isEmpty()) {
            $this->warn('No se encontraron slots disponibles en los próximos 7 días con los horarios actuales.');

            return self::SUCCESS;
        }

        // Mezclar aleatoriamente los slots candidatos para distribuir entre profesionales y días
        $shuffledCandidates = $candidateSlots->shuffle();

        $createdAppointments = collect();
        $faker = fake('es_AR');

        foreach ($shuffledCandidates as $candidate) {
            if ($createdAppointments->count() >= $count) {
                break;
            }

            /** @var Doctor $doctor */
            $doctor = $candidate['doctor'];
            /** @var Carbon $date */
            $date = $candidate['date'];
            $time = $candidate['time'];

            // Elegir especialidad vinculada al médico si tiene
            $specialty = $doctor->specialties->isNotEmpty() ? $doctor->specialties->random() : null;

            // Elegir obra social vinculada o particular (null)
            $availableInsurances = $doctor->healthInsurances;
            $healthInsurance = null;
            if ($availableInsurances->isNotEmpty() && $faker->boolean(70)) {
                $healthInsurance = $availableInsurances->random();
            }

            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $dni = (string) $faker->unique()->numberBetween(10000000, 48000000);
            $email = strtolower("{$firstName}.{$lastName}.{$dni}@ejemplo.com");
            $email = preg_replace('/[^a-z0-9.@_-]/', '', $email);

            $patientData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => '11'.$faker->numerify('########'),
                'dni' => $dni,
                'specialty_id' => $specialty?->id,
                'health_insurance_id' => $healthInsurance?->id,
                'notes' => 'Turno simulado vía appointments:simulate',
                'ip_address' => '127.0.0.1',
            ];

            try {
                $appointment = $bookingService->book($doctor, $date, $time, $patientData);

                // Enviar mail de confirmación de solicitud igual que el wizard
                try {
                    Mail::to($appointment->email)->send(new AppointmentRequested($appointment));
                } catch (\Throwable $mailException) {
                    // Si el servidor de correos no está disponible en local, continuar
                }

                $createdAppointments->push($appointment);
            } catch (\Throwable $e) {
                // Si el slot fue tomado o no está disponible, continuar con el siguiente
                continue;
            }
        }

        if ($createdAppointments->isEmpty()) {
            $this->warn('No fue posible crear ningún turno simulado.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("✓ Se crearon exitosamente {$createdAppointments->count()} turnos de prueba:");
        $this->newLine();

        $tableRows = $createdAppointments->map(function ($appointment) {
            return [
                'ID' => $appointment->id,
                'Fecha' => $appointment->date->format('d/m/Y'),
                'Hora' => substr($appointment->start_time, 0, 5).' hs',
                'Profesional' => $appointment->doctor->full_name,
                'Especialidad' => $appointment->specialty?->name ?? '—',
                'Obra Social' => $appointment->healthInsurance?->name ?? 'Particular',
                'Paciente' => $appointment->patient_name,
                'DNI' => $appointment->dni,
                'Email' => $appointment->email,
                'Estado' => $appointment->status->label(),
            ];
        });

        $this->table(
            ['ID', 'Fecha', 'Hora', 'Profesional', 'Especialidad', 'Obra Social', 'Paciente', 'DNI', 'Email', 'Estado'],
            $tableRows
        );

        $this->newLine();

        return self::SUCCESS;
    }
}
