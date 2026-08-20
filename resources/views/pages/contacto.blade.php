@extends('layouts.public')

@section('title', 'Contacto | Consultorio Integral Arenales')
@section('description', 'Contactá al Consultorio Integral Arenales: horarios, teléfono, email y ubicación en Recoleta, Capital Federal.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/contacto.css') }}" />
@endpush

@section('content')
<!-- Banda de título -->
    <header class="page-band">
      <div class="container">
        <h1>Contacto</h1>
      </div>
    </header>

    <!-- Contacto -->
    <section class="py-5">
      <div class="container-xxl py-3">
        <div class="row justify-content-center">
          <div class="col-lg-7 col-xl-6 contact-wrap">
            <h2 class="mb-1">Consultorio Integral Arenales</h2>
            <p class="lead-sm">Cuidamos cada etapa de tu vida.</p>

            <div class="contact-item">
              <span class="ci-icon"><i class="bi bi-clock"></i></span>
              <p>Lunes a Viernes de 9 a 20hs</p>
            </div>
            <div class="contact-item">
              <span class="ci-icon"><i class="bi bi-phone"></i></span>
              <a href="tel:+541148223473">4822.3473</a>
            </div>
            <div class="contact-item">
              <span class="ci-icon"><i class="bi bi-envelope-fill"></i></span>
              <a href="mailto:consultas@consultoriointegralarenales.com.ar">consultas@consultoriointegralarenales.com.ar</a>
            </div>
            <div class="contact-item">
              <span class="ci-icon"><i class="bi bi-geo-alt-fill"></i></span>
              <p>Azcuenaga 1222 5to piso<br />Recoleta, Capital Federal</p>
            </div>

            <div class="mt-4">
              <iframe
                class="map-frame"
                src="https://www.google.com/maps?q=Azcu%C3%A9naga%201222,%20CABA&output=embed"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Mapa de Azcuénaga 1222, Recoleta, Capital Federal"
              ></iframe>
            </div>
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
  <script src="{{ asset('assets/js/contacto.js') }}"></script>
@endpush
