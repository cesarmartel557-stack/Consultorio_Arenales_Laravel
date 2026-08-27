@extends('layouts.public')

@section('title', 'Especialidades | Consultorio Integral Arenales')
@section('description', 'Conocé nuestras especialidades: ginecología, obstetricia, fertilidad y mastología.')

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/especialidades.css') }}" />
@endpush

@section('content')
<!-- Banda de título -->
    <header class="page-band" id="especialidades">
      <div class="container">
        <h1>Especialidades</h1>
      </div>
    </header>

    <!-- Detalle: {{ $specialty->name }} -->
    <section class="py-5" id="{{ $specialty->slug }}">
      <div class="container py-3">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <img
              class="esp-photo"
              src="{{ asset('assets/images/midsection-doctors-with-arms-crossed-2.webp') }}"
              alt="{{ $specialty->name }}"
            />
          </div>
          <div class="col-lg-6 offset-lg-1 esp-detail">
            <div class="esp-round-icon"><img src="{{ $specialty->icon ? Storage::url($specialty->icon) : asset('assets/images/logo_300.png') }}" alt="{{ $specialty->name }}" /></div>
            <h2 class="mb-3">{{ $specialty->name }}</h2>
            @if ($specialty->description)
              <p class="mb-0">{{ $specialty->description }}</p>
            @endif
          </div>
        </div>
      </div>
    </section>

    @if ($specialty->doctors->isNotEmpty())
    <!-- Profesionales de {{ $specialty->name }} -->
    <section class="py-5">
      <div class="container">
        <h3 class="mb-5 text-center">Profesionales en {{ $specialty->name }}</h3>
        <div class="doc-grid" id="grid-{{ $specialty->slug }}">

          @foreach ($specialty->doctors as $index => $doctor)
          <div class="doc-item" data-aos="fade-up" @if($index > 0) data-aos-delay="{{ $index * 100 }}" @endif>
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="{{ $doctor->photo ? Storage::url($doctor->photo) : asset('assets/images/logo_300.png') }}" alt="{{ $doctor->full_name }}" />
                </div>
                <div class="col">
                  <h3>{{ $doctor->full_name }}</h3>
                  @if ($doctor->license)
                    <div class="doc-mn">{{ $doctor->license }}</div>
                  @endif
                  <p class="doc-role">{{ $doctor->headline ?: $doctor->specialties->pluck('name')->join(' · ') }}</p>

                  @if ($doctor->bio)
                    <div class="doc-bio mb-3">{!! nl2br(e($doctor->bio)) !!}</div>
                  @endif

                  @php
                    $displayInsurances = $doctor->healthInsurances->filter(function ($insurance) {
                        return strtolower(trim($insurance->name)) !== 'particular';
                    });
                  @endphp
                  @if ($displayInsurances->isNotEmpty())
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                    <span class="doc-os-label me-1">Obras Sociales</span>
                    @foreach ($displayInsurances as $insurance)
                      @if($insurance->logo)
                        <span class="obra-soc"><img src="{{ Storage::url($insurance->logo) }}" alt="{{ $insurance->name }}" /></span>
                      @else
                        <span class="obra-soc">{{ $insurance->name }}</span>
                      @endif
                    @endforeach
                  </div>
                  @endif

                  @if ($doctor->schedules->isNotEmpty())
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    @foreach ($doctor->schedules->sortBy('weekday') as $schedule)
                      <div class="chip"><b>{{ \App\Models\DoctorSchedule::WEEKDAYS[$schedule->weekday] ?? '' }}</b>{{ substr($schedule->start_time, 0, 5) }} a {{ substr($schedule->end_time, 0, 5) }}</div>
                    @endforeach
                  </div>
                  @endif

                  <a href="{{ route('turnos.doctor', $doctor) }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
            </div>
          </div>
          @endforeach

        </div>
      </div>
    </section>
    @endif


    <!-- CTA -->
    <section class="pb-5" id="contacto">
      <div class="container">
        <div class="cta-band p-4 p-md-4">
          <div class="row align-items-center g-3">
            <div class="col-auto"><div class="agenda-icon"><img class="" src="{{ asset('assets/images/agenda-icon.webp') }}" alt="" /></div></div>
            <div class="col">
              <h3 class="mb-1">Agenda tu turno de forma rápida y sencilla.</h3>
              <p>Elegí el profesional, la fecha y el horario que mejor se adapten a vos.</p>
            </div>
            <div class="col-12 col-md-auto">
              <a href="{{ route('profesionales') }}" class="btn btn-cta w-100">Solicitar Turno</a>
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- Footer -->
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="{{ asset('assets/js/especialidades.js') }}"></script>
@endpush
