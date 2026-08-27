<x-mail::message>
# Recordatorio de tu turno

Hola **{{ $appointment->first_name }}**, te recordamos que mañana tenés tu consulta médica en el consultorio.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}  
**Cuándo:** {{ $when }}  
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

Te pedimos por favor llegar **10 minutos antes**.

<x-mail::button :url="$manageUrl">
Ver o gestionar mi turno
</x-mail::button>

Si no vas a poder asistir, por favor cancelalo con tiempo desde el enlace para reasignar el horario.

Te esperamos,<br>
**Consultorio Integral Arenales**
</x-mail::message>
