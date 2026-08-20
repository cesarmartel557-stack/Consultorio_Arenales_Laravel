<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

abstract class AppointmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing('doctor');
    }

    abstract protected function markdownView(): string;

    public function content(): Content
    {
        return new Content(
            markdown: $this->markdownView(),
            with: [
                'appointment' => $this->appointment,
                'doctor' => $this->appointment->doctor,
                'manageUrl' => $this->manageUrl(),
                'when' => $this->formattedDate(),
            ],
        );
    }

    protected function manageUrl(): string
    {
        return URL::signedRoute('turnos.gestion', $this->appointment, now()->addDays(90));
    }

    protected function formattedDate(): string
    {
        $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        return sprintf(
            '%s %s a las %s hs',
            $days[$this->appointment->date->dayOfWeek],
            $this->appointment->date->format('d/m/Y'),
            substr($this->appointment->start_time, 0, 5),
        );
    }
}
