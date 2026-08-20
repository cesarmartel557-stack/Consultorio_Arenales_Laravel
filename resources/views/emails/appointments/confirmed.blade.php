<x-mail::message>
# ¡Tu turno está confirmado!

Hola {{ $appointment->first_name }}, el consultorio confirmó tu turno.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}
**Cuándo:** {{ $when }}
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

Te adjuntamos el turno para que lo agendes en tu calendario. Te pedimos llegar 10 minutos antes.

<x-mail::button :url="$manageUrl">
Ver o cancelar mi turno
</x-mail::button>

Si no vas a poder asistir, cancelalo con anticipación así liberamos el horario.

Nos vemos,<br>
{{ config('app.name') }}
</x-mail::message>
