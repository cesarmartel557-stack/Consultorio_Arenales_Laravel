<?php

namespace App\Mail;

use App\Services\CalendarInvite;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Envelope;

class AppointmentReminder extends AppointmentMail
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio: tenés turno mañana');
    }

    protected function markdownView(): string
    {
        return 'emails.appointments.reminder';
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
