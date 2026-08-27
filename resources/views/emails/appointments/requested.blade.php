<x-mail::message>
# Recibimos tu solicitud de turno

Hola **{{ $appointment->first_name }}**, registramos tu pedido de turno correctamente.

Tené en cuenta que **todavía no está confirmado**: el equipo del consultorio lo revisará y te enviaremos una notificación ni bien quede confirmado.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}  
**Cuándo:** {{ $when }}  
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

<x-mail::button :url="$manageUrl">
Ver o gestionar mi turno
</x-mail::button>

Si no vas a poder asistir, por favor cancelalo con anticipación desde el botón para liberar el horario para otra persona.

Saludos cordiales,<br>
**Consultorio Integral Arenales**
</x-mail::message>
