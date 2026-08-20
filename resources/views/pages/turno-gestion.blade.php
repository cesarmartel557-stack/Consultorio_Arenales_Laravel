@extends('layouts.public')

@section('title', 'Mi turno | Consultorio Integral Arenales')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/turnos-gracias.css') }}" />
@endpush

@section('content')
@php
  $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  $doctor = $appointment->doctor;
@endphp

<header class="page-band">
  <div class="container"><h1>Mi turno</h1></div>
</header>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="booking-card">
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="thanks">
            <h3>Hola {{ $appointment->first_name }}</h3>
            <p>
              Estado de tu turno:
              <strong>{{ $appointment->status->label() }}</strong>
            </p>
          </div>

          <div class="slot-summary my-4">
            <div class="d-flex align-items-center gap-3">
              <div class="ico"><i class="bi bi-person-badge"></i></div>
              <div>
                <small>Profesional</small>
                <b>{{ $doctor->full_name }}</b>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <div class="ico"><i class="bi bi-calendar2-check"></i></div>
              <div>
                <small>Fecha y hora</small>
                <b>{{ $dias[$appointment->date->dayOfWeek] }} {{ $appointment->date->format('d/m/Y') }} · {{ substr($appointment->start_time, 0, 5) }} hs</b>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <div class="ico"><i class="bi bi-geo-alt"></i></div>
              <div>
                <small>Dónde</small>
                <b>Azcuénaga 1222, 5to piso</b>
              </div>
            </div>
          </div>

          @if ($appointment->isManageableByPatient())
            <form method="POST" action="{{ url()->signedRoute('turnos.cancelar', $appointment) }}">
              @csrf
              <div class="field mb-3">
                <textarea name="reason" class="form-control" rows="2" maxlength="300" placeholder="Motivo (opcional)"></textarea>
              </div>
              <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 thanks-actions">
                <button type="submit" class="btn btn-outline-brand" onclick="return confirm('¿Confirmás la cancelación de tu turno?')">
                  Cancelar turno
                </button>
                <a class="btn btn-brand" href="{{ route('turnos.doctor', $doctor) }}">Pedir otro horario</a>
              </div>
            </form>
          @else
            <div class="d-flex justify-content-center thanks-actions">
              <a class="btn btn-brand" href="{{ route('profesionales') }}">Solicitar un nuevo turno</a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
