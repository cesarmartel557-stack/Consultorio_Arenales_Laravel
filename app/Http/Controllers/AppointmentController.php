<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelledByPatient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function thanks(Appointment $appointment)
    {
        $appointment->load(['doctor.specialties', 'doctor.schedules']);

        return view('pages.turnos-gracias', compact('appointment'));
    }

    /**
     * Gestión del turno sin cuenta: el link firmado enviado por mail es la credencial.
     */
    public function manage(Appointment $appointment)
    {
        $appointment->load(['doctor', 'healthInsurance', 'specialty']);

        return view('pages.turno-gestion', compact('appointment'));
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        if (! $appointment->isManageableByPatient()) {
            return back()->with('error', 'Este turno ya no puede cancelarse online. Comunicate con el consultorio.');
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_by' => 'patient',
            'status_reason' => $request->input('reason'),
            'cancelled_at' => now(),
        ]);

        Mail::to(config('mail.from.address'))->send(new AppointmentCancelledByPatient($appointment));

        return back()->with('success', 'Tu turno fue cancelado. Podés solicitar uno nuevo cuando quieras.');
    }
}
