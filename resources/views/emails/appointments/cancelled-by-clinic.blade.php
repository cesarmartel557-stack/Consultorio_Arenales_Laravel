<x-mail::message>
# Tu turno fue cancelado

Hola {{ $appointment->first_name }}, tuvimos que cancelar tu turno con {{ $doctor->full_name }} del {{ $when }}.

@if ($appointment->status_reason)
<x-mail::panel>
{{ $appointment->status_reason }}
</x-mail::panel>
@endif

Podés solicitar un nuevo horario cuando quieras:

<x-mail::button :url="route('turnos.doctor', $doctor)">
Elegir otro horario
</x-mail::button>

Disculpá las molestias,<br>
{{ config('app.name') }}
</x-mail::message>
