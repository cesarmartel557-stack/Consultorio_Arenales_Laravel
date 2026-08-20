<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso interno al consultorio: el paciente canceló desde su link.
 */
class AppointmentCancelledByPatient extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Un paciente canceló su turno: '.$this->appointment->patient_name,
        );
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.cancelled-by-patient';
    }
}
