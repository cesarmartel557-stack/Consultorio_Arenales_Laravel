<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelledByClinic;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRejected;
use App\Models\Appointment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class AppointmentWorkflow
{
    public function confirm(Appointment $appointment): void
    {
        $appointment->update([
            'status' => AppointmentStatus::Confirmed,
            'confirmed_at' => now(),
        ]);

        dispatch(function () use ($appointment) {
            try {
                Mail::to($appointment->email)->send(new AppointmentConfirmed($appointment));
            } catch (\Throwable $e) {
                report($e);
            }
        })->afterResponse();

        $this->notify('Turno confirmado', "Se le envió el mail de confirmación a {$appointment->email}.");
    }

    public function reject(Appointment $appointment, ?string $reason = null): void
    {
        $appointment->update([
            'status' => AppointmentStatus::Rejected,
            'status_reason' => $reason,
        ]);

        dispatch(function () use ($appointment) {
            try {
                Mail::to($appointment->email)->send(new AppointmentRejected($appointment));
            } catch (\Throwable $e) {
                report($e);
            }
        })->afterResponse();

        $this->notify('Turno rechazado', 'El horario quedó liberado y se avisó al paciente.');
    }

    public function cancel(Appointment $appointment, ?string $reason = null): void
    {
        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_by' => 'clinic',
            'status_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        dispatch(function () use ($appointment) {
            try {
                Mail::to($appointment->email)->send(new AppointmentCancelledByClinic($appointment));
            } catch (\Throwable $e) {
                report($e);
            }
        })->afterResponse();

        $this->notify('Turno cancelado', 'El horario quedó liberado y se avisó al paciente.');
    }

    private function notify(string $title, string $body): void
    {
        Notification::make()->title($title)->body($body)->success()->send();
    }
}
