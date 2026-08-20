<x-mail::message>
# No pudimos confirmar tu turno

Hola {{ $appointment->first_name }}, lamentablemente no pudimos confirmar el turno que solicitaste con {{ $doctor->full_name }} para el {{ $when }}.

@if ($appointment->status_reason)
<x-mail::panel>
{{ $appointment->status_reason }}
</x-mail::panel>
@endif

Podés elegir otro horario disponible acá:

<x-mail::button :url="route('turnos.doctor', $doctor)">
Elegir otro horario
</x-mail::button>

Disculpá las molestias,<br>
{{ config('app.name') }}
</x-mail::message>
