<?php

use App\Http\Controllers\AppointmentController;
use App\Livewire\BookingWizard;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/nosotros', 'pages.nosotros')->name('nosotros');
Route::view('/contacto', 'pages.contacto')->name('contacto');

Route::get('/especialidades', function () {
    return view('pages.especialidades', [
        'specialties' => Specialty::where('is_active', true)->orderBy('sort_order')->get(),
    ]);
})->name('especialidades');

Route::get('/profesionales', function () {
    return view('pages.profesionales', [
        'doctors' => Doctor::active()
            ->with(['specialties', 'healthInsurances', 'schedules'])
            ->orderBy('sort_order')
            ->get(),
    ]);
})->name('profesionales');

Route::get('/turnos/{doctor}', BookingWizard::class)->name('turnos.doctor');

Route::get('/turnos/gracias/{appointment}', [AppointmentController::class, 'thanks'])
    ->name('turnos.gracias');

Route::get('/mi-turno/{appointment}', [AppointmentController::class, 'manage'])
    ->middleware('signed')
    ->name('turnos.gestion');

Route::post('/mi-turno/{appointment}/cancelar', [AppointmentController::class, 'cancel'])
    ->middleware('signed')
    ->name('turnos.cancelar');
