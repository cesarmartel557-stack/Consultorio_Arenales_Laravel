@extends('layouts.public')

@section('title', 'Turno solicitado | Consultorio Integral Arenales')
@section('description', 'Tu solicitud de turno fue registrada correctamente.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/turnos-gracias.css') }}" />
@endpush

@section('content')
@php
  use Illuminate\Support\Carbon;
  $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  $doctor = $appointment->doctor;
@endphp

<header class="page-band">
  <div class="container"><h1>Turno solicitado correctamente</h1></div>
</header>

<section class="py-5">
  <div class="container">
    @if ($doctor->specialties->isNotEmpty())
      <div class="filters-scroll mb-4">
        <div class="d-flex justify-content-lg-center gap-3 pb-2" style="min-width: max-content; margin: 0 auto;">
          @foreach ($doctor->specialties as $specialty)
            <div class="filter-btn is-static">
              @if ($specialty->icon)
                <div class="filter-ico"><img src="{{ Storage::url($specialty->icon) }}" alt="" /></div>
              @endif
              <span>{{ $specialty->name }}</span>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="booking-card">
          <div class="row g-5 align-items-start">
            <div class="col-12 col-sm-auto text-center text-sm-start">
              <img class="doc-photo" src="{{ $doctor->photo ? Storage::url($doctor->photo) : asset('assets/images/logo_300.png') }}" alt="{{ $doctor->full_name }}" />
            </div>
            <div class="col">
              <h2>{{ $doctor->full_name }}</h2>
              @if ($doctor->license)
                <div class="doc-mn">{{ $doctor->license }}</div>
              @endif
              <p class="doc-role">{{ $doctor->headline ?: $doctor->specialties->pluck('name')->join(' · ') }}</p>

              @if ($doctor->bio)
                <div class="doc-bio mb-3">{!! nl2br(e($doctor->bio)) !!}</div>
              @endif

              <div class="d-flex flex-wrap gap-3 doc-hours">
                @foreach ($doctor->schedules as $schedule)
                  <div class="chip"><b>{{ $schedule->weekday_name }}</b>{{ substr($schedule->start_time, 0, 5) }} a {{ substr($schedule->end_time, 0, 5) }}</div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="booking-sep"></div>

          <div class="thanks">
            <div class="check"><i class="bi bi-check-lg"></i></div>
            <h3>Gracias, tu solicitud fue registrada</h3>
            <p>Te enviamos el detalle por mail. El consultorio va a confirmarte el turno a la brevedad.</p>
          </div>

          <div class="slot-summary mt-4 my-5">
            <div class="d-flex align-items-center gap-3">
              <div class="ico"><i class="bi bi-calendar2-check"></i></div>
              <div>
                <small>Turno solicitado</small>
                <b>{{ $dias[$appointment->date->dayOfWeek] }} {{ $appointment->date->format('d/m') }} · {{ substr($appointment->start_time, 0, 5) }} hs</b>
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

          <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 thanks-actions">
            <a class="btn btn-brand" href="{{ route('profesionales') }}">Solicitar otro Turno</a>
            <a class="btn btn-outline-brand" href="{{ route('home') }}">Cerrar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
