<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Envelope;

class AppointmentCancelledByClinic extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu turno fue cancelado');
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.cancelled-by-clinic';
    }
}
