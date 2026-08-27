<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone: string, specialty_id?: int|null, health_insurance_id?: int|null, notes?: string|null, ip_address?: string|null}  $patient
     *
     * @throws SlotUnavailableException
     */
    public function book(Doctor $doctor, Carbon $date, string $time, array $patient): Appointment
    {
        if (! $this->availability->isSlotAvailable($doctor, $date, $time)) {
            throw new SlotUnavailableException(
                'Ese horario ya no está disponible. Elegí otro por favor.'
            );
        }

        $length = $this->availability->slotLength($doctor, $date, $time);

        try {
            return Appointment::create([
                ...$patient,
                'doctor_id' => $doctor->id,
                'date' => $date->toDateString(),
                'start_time' => $time,
                'end_time' => $date->copy()->setTimeFromTimeString($time)->addMinutes($length)->format('H:i'),
                'status' => AppointmentStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Otro paciente tomó el mismo horario entre la validación y el insert.
            throw new SlotUnavailableException(
                'Ese horario acaba de ser reservado por otra persona. Elegí otro por favor.'
            );
        }
    }

    /**
     * Solicitud vigente del mismo paciente (por DNI) con el mismo profesional, para no duplicar.
     */
    public function existingRequestFor(Doctor $doctor, string $dni): ?Appointment
    {
        return Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('dni', $dni)
            ->blocking()
            ->upcoming()
            ->orderBy('date')
            ->first();
    }
}
