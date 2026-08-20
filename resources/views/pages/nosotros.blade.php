@extends('layouts.public')

@section('title', 'Nosotros | Consultorio Integral Arenales')
@section('description', 'Conocé al equipo del Consultorio Integral Arenales: atención médica de excelencia, cercana y con tecnología de vanguardia.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/nosotros.css') }}" />
@endpush

@section('content')
<!-- Banda de título -->
    <header class="page-band">
      <div class="container">
        <h1>Nosotros</h1>
      </div>
    </header>

    <!-- Compromiso + collage -->
    <!-- Compromiso + collage -->
    <section class="collage py-5">
      <div class="container py-3">
        <div class="row g-5 align-items-start">
          <div class="col-lg-6 about-collage">
            <img
              class="main mb-3"
              src="/assets/images/nosotros-img-1.webp"
              alt=""
            />
            <div class="row g-3">
              <div class="col-6">
                <img
                  class="thumb"
                  src="/assets/images/nosotros-img-2.webp"
                  alt=""
                />
              </div>
              <div class="col-6">
                <img
                  class="thumb"
                   src="/assets/images/nosotros-img-3.webp"
                   alt=""
                />
              </div>
            </div>
          </div>
          <div class="col-lg-6 about-text">
            <h2 class="section-title h3 mb-4">Comprometidos con una atención médica de excelencia.</h2>
            <p>En Consultorio Integral Arenales creemos que la medicina comienza con la escucha.</p>
            <p>
              Desde nuestros inicios trabajamos para brindar un servicio basado en el respeto, la confianza y el
              acompañamiento personalizado, integrando profesionales de distintas especialidades para ofrecer una
              atención completa y coordinada.
            </p>
            <p>
              Nuestra misión es cuidar la salud de nuestros pacientes con calidad humana, actualización permanente y
              tecnología de vanguardia.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Especialidades (informativas) -->
    <section class="section-muted py-5">
      <div class="container">
        <div class="row g-3 justify-content-center">
          <div class="col-6 col-md-4 col-lg-2">
            <div class="esp-card">
              <div class="esp-icon"><img src="/assets/images/icon-gineco.webp" alt="Ginecología" /></div>
              <h4>Ginecología</h4>
            </div>
          </div>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="esp-card">
              <div class="esp-icon"><img src="/assets/images/icon-obstetricia.webp" alt="Obstetricia" /></div>
              <h4>Obstetricia</h4>
            </div>
          </div>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="esp-card">
              <div class="esp-icon"><img src="/assets/images/icon-fertilidad.webp" alt="Fertilidad" /></div>
              <h4>Fertilidad</h4>
            </div>
          </div>
          <div class="col-6 col-md-4 col-lg-2">
            <div class="esp-card">
              <div class="esp-icon"><img src="/assets/images/icon-mastologia.webp" alt="Mastología" /></div>
              <h4>Mastología</h4>
            </div>
          </div>
          <!--
          <div class="col-6 col-md-4 col-lg-2">
            <div class="esp-card">
              <div class="esp-icon"><img src="/assets/images/icon-nutricion.webp" alt="Nutrición" /></div>
              <h4>Nutrición</h4>
            </div>
          </div>
          -->
          
        </div>
      </div>
    </section>

    <!-- Equipo -->
    <section class="py-5 team">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <h2 class="section-title h3 mb-4">Ciencia para cuidar.<br />Cercanía para acompañar.</h2>
            <p class="text-muted" style="font-size: 0.85rem">
              Cada profesional de Consultorio Integral Arenales comparte una misma filosofía: brindar atención
              cercana, escucha activa y excelencia médica en cada consulta.
            </p>
            <p class="text-muted" style="font-size: 0.85rem">
              Nuestro trabajo interdisciplinario nos permite ofrecer un abordaje integral para acompañarte con
              confianza y seguridad.
            </p>
            <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-2">Conocé a nuestro equipo</a>
          </div>
          <div class="col-lg-7">
            <img
              src="/assets/images/midsection-doctors-with-arms-crossed.webp"
              alt="Equipo"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="pb-5" id="contacto">
      <div class="container">
        <div class="cta-band p-4 p-md-4">
          <div class="row align-items-center g-3">
            <div class="col-auto"><div class="agenda-icon"><img class="" src="/assets/images/agenda-icon.webp" alt="" /></div></div>
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
  <script src="{{ asset('assets/js/nosotros.js') }}"></script>
@endpush
