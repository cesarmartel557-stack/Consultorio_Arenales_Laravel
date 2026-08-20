<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Envelope;

class AppointmentRequested extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recibimos tu solicitud de turno');
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.requested';
    }
}
