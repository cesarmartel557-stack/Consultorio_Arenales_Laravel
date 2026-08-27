<?php

namespace App\Mail;

use App\Services\CalendarInvite;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Envelope;

class AppointmentConfirmed extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu turno está confirmado');
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.confirmed';
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => app(CalendarInvite::class)->for($this->appointment), 'turno.ics')
                ->withMime('text/calendar'),
        ];
    }
}
