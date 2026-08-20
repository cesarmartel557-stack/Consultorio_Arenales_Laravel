<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Carbon;

class CalendarInvite
{
    public function for(Appointment $appointment): string
    {
        $start = Carbon::parse($appointment->date->format('Y-m-d').' '.$appointment->start_time);
        $end = Carbon::parse($appointment->date->format('Y-m-d').' '.$appointment->end_time);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Consultorio Integral Arenales//Turnos//ES',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$appointment->uuid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$end->utc()->format('Ymd\THis\Z'),
            'SUMMARY:Turno con '.$this->escape($appointment->doctor->full_name),
            'LOCATION:Azcuénaga 1222 5to piso, Palermo, CABA',
            'DESCRIPTION:'.$this->escape('Consultorio Integral Arenales. Tel: 4822 3473'),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines);
    }

    private function escape(string $value): string
    {
        return str_replace([',', ';'], ['\,', '\;'], $value);
    }
}
