<x-mail::message>
# Te recordamos tu turno

Hola {{ $appointment->first_name }}, mañana tenés turno en el consultorio.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}
**Cuándo:** {{ $when }}
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

Te pedimos llegar 10 minutos antes. Si no vas a poder asistir, avisanos:

<x-mail::button :url="$manageUrl">
Cancelar mi turno
</x-mail::button>

Nos vemos,<br>
{{ config('app.name') }}
</x-mail::message>
