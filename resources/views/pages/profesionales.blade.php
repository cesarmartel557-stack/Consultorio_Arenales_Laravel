@extends('layouts.public')

@section('title', 'Profesionales | Consultorio Integral Arenales')
@section('description', '')

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/profesionales.css') }}" />
@endpush

@section('content')
<!-- Banda de título -->
    <header class="page-band">
      <div class="container">
        <h1>Profesionales</h1>
      </div>
    </header>

    <!-- Grilla de profesionales -->
    <section class="py-5">
      <div class="container">

        <div class="doc-grid" id="grid">

          @forelse ($doctors as $doctor)
            <div class="doc-item" data-aos="fade-up">
              <div class="doc-card">
                <div class="row g-4 align-items-center">
                  <div class="col-auto">
                    <img class="doc-photo" src="{{ $doctor->photo ? asset($doctor->photo) : asset('assets/images/logo_300.png') }}" alt="{{ $doctor->full_name }}" />
                  </div>
                  <div class="col">
                    <h3>{{ $doctor->full_name }}</h3>
                    @if ($doctor->license)
                      <div class="doc-mn">{{ $doctor->license }}</div>
                    @endif
                    <p class="doc-role">{{ $doctor->headline }}</p>

                    @if ($doctor->healthInsurances->isNotEmpty())
                      <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                        <span class="doc-os-label me-1">Obras Sociales</span>
                        @foreach ($doctor->healthInsurances->whereNotNull('logo') as $insurance)
                          <span class="obra-soc"><img src="{{ asset($insurance->logo) }}" alt="{{ $insurance->name }}" /></span>
                        @endforeach
                      </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                      @foreach ($doctor->schedules as $schedule)
                        <div class="chip"><b>{{ $schedule->weekday_name }}</b>{{ substr($schedule->start_time, 0, 5) }} a {{ substr($schedule->end_time, 0, 5) }}</div>
                      @endforeach
                    </div>

                    <a href="{{ route('turnos.doctor', $doctor) }}" class="btn btn-brand">Solicitar Turno</a>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <p class="text-center py-5">No hay profesionales disponibles por el momento.</p>
          @endforelse
        </div>
      </div>
    </section>


    <!-- Footer -->
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="{{ asset('assets/js/profesionales.js') }}"></script>
@endpush
