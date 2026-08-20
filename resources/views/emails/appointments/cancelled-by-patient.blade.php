<x-mail::message>
# Un paciente canceló su turno

<x-mail::panel>
**Paciente:** {{ $appointment->patient_name }}
**Contacto:** {{ $appointment->email }} — {{ $appointment->phone }}
**Profesional:** {{ $doctor->full_name }}
**Turno cancelado:** {{ $when }}
</x-mail::panel>

@if ($appointment->status_reason)
**Motivo:** {{ $appointment->status_reason }}
@endif

El horario quedó liberado automáticamente en la agenda.
</x-mail::message>
