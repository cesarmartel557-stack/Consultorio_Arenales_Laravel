<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Envelope;

class AppointmentRejected extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'No pudimos confirmar tu turno');
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.rejected';
    }
}
