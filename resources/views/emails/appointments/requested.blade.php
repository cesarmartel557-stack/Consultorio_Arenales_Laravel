<x-mail::message>
# Recibimos tu solicitud, {{ $appointment->first_name }}

Registramos tu pedido de turno. **Todavía no está confirmado**: el consultorio lo revisa y te avisa por mail apenas quede confirmado.

<x-mail::panel>
**Profesional:** {{ $doctor->full_name }}
**Cuándo:** {{ $when }}
**Dónde:** Azcuénaga 1222, 5to piso — Palermo, CABA
</x-mail::panel>

<x-mail::button :url="$manageUrl">
Ver o cancelar mi turno
</x-mail::button>

Si no podés asistir, cancelá desde ese botón así liberamos el horario para otra persona.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
