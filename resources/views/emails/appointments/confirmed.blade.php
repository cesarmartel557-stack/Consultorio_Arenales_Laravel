<x-mail::message>
# ¡Tu turno está confirmado!

Hola **{{ $appointment->first_name }}**, te confirmamos que tu turno ha sido agendado exitosamente.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}  
**Cuándo:** {{ $when }}  
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

Te pedimos por favor presentarte **10 minutos antes** del horario indicado.

<x-mail::button :url="$manageUrl">
Ver o gestionar mi turno
</x-mail::button>

Si surge algún imprevisto y no podés asistir, podés cancelar el turno con anticipación desde el botón superior.

Te esperamos,<br>
**Consultorio Integral Arenales**
</x-mail::message>
