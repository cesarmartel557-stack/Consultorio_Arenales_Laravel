<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:remind';

    protected $description = 'Envía el recordatorio por mail de los turnos confirmados de mañana';

    public function handle(): int
    {
        $appointments = Appointment::query()
            ->with('doctor')
            ->where('status', AppointmentStatus::Confirmed)
            ->whereDate('date', today()->addDay())
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($appointments as $appointment) {
            Mail::to($appointment->email)->send(new AppointmentReminder($appointment));
            $appointment->update(['reminder_sent_at' => now()]);
        }

        $this->info("Recordatorios enviados: {$appointments->count()}");

        return self::SUCCESS;
    }
}
