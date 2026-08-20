@extends('layouts.public')

@section('title', 'Consultorio Integral Arenales | Medicina especializada')
@section('description', 'Atención médica personalizada con un equipo de profesionales especializados y tecnología de última generación.')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
@endpush

@section('content')
<!-- Hero -->
    <header class="hero py-5" id="inicio">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <h1 class="display-6 mb-4">Medicina especializada para acompañarte en cada etapa de tu vida.</h1>
            <p class="mb-4">
              En <strong class="text-brand">Consultorio Integral Arenales</strong> brindamos atención médica
              personalizada con un equipo de profesionales especializados, tecnología de última generación y un
              enfoque centrado en vos y tu bienestar.
            </p>
            <div class="d-flex flex-column align-items-start gap-2">
              <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
              <a href="{{ route('especialidades') }}" class="btn btn-brand">Conocé nuestras especialidades</a>
            </div>
          </div>
          <div class="col-lg-7 hero-collage">
            <img
              class="img-a"
              src="/assets/images/close-up-medicine-doctor-offering-helping-hand-handshake-partnership-trust-concept.webp"
              alt=""
            />
            <img
              class="img-b"
              src="/assets/images/doctor-performing-ultrasound-pregnant-woman.webp"
              alt=""
            />
            <img
              class="img-c"
              src="/assets/images/doctor-patient-discussing-something-while-sitting-table-medicine-health-care-concept.webp"
              alt=""
            />

          </div>

        </div>
      </div>
    </header>

    <!-- Diferenciales -->
    <section class="py-5">
      <div class="container py-3">
        <div class="row g-4">
          <div class="col-md-4">
            <div class="feature-card">
              <div class="diferenciales-icon"><img class="" src="/assets/images/atencion-icon.png" alt="" /></div>
              <h3>Atención personalizada</h3>
              <p>Cada consulta comienza escuchando tus necesidades.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="diferenciales-icon"><img class="" src="/assets/images/profesionales-icon.webp" alt="" /></div>
              <h3>Profesionales especializados</h3>
              <p>Un equipo médico con amplia experiencia y formación continua.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="diferenciales-icon"><img class="" src="/assets/images/tecnologia-icon.webp" alt="" /></div>
              <h3>Tecnología de última generación</h3>
              <p>Equipamiento moderno para diagnósticos precisos y tratamientos de calidad.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Especialidades (Swiper) -->
    <section class="section-muted py-5" id="especialidades">
      <div class="container py-4">
        <h2 class="section-title text-center h4 mb-3">¿Cómo podemos ayudarte?</h2>
        <p class="text-center text-muted mx-auto mb-5" style="max-width: 620px; font-size: 0.85rem">
          Contamos con un equipo interdisciplinario que acompaña la salud de la mujer y de toda la familia,
          ofreciendo atención integral con un enfoque preventivo, diagnóstico y terapéutico.
        </p>

        <div class="swiper" id="swiperEspecialidades">
          <div class="swiper-wrapper">
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="/assets/images/icon-gineco.webp" alt="" /></div>
                <h4>Ginecología</h4>
                <p>
                  Prevención, diagnóstico y tratamiento de enfermedades ginecológicas. Realizamos controles
                  periódicos y acompañamos cada etapa de la salud femenina.
                </p>
                <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="/assets/images/icon-obstetricia.webp" alt="" /></div>
                <h4>Obstetricia</h4>
                <p>
                  Acompañamiento médico durante el embarazo, parto y puerperio, brindando seguimiento
                  personalizado para cuidar a mamá y al bebé.
                </p>
                <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="/assets/images/icon-fertilidad.webp" alt="" /></div>
                <h4>Fertilidad</h4>
                <p>
                  Evaluación y asesoramiento para quienes buscan lograr un embarazo, con un enfoque profesional
                  y humano.
                </p>
                <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="/assets/images/icon-mastologia.webp" alt="" /></div>
                <h4>Mastología</h4>
                <p>
                  Prevención, detección temprana y tratamiento de patologías mamarias mediante estudios y
                  controles especializados.
                </p>
                <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            <!--
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="/assets/images/icon-nutricion.webp" alt="" /></div>
                <h4>Nutrición</h4>
                <p>
                  Planes nutricionales personalizados para mejorar tu calidad de vida, prevenir enfermedades y
                  alcanzar tus objetivos de salud.
                </p>
                <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            -->
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </div>
    </section>

    <!-- Equipo -->
    <section class="py-5 team" id="equipo">
      <div class="container py-4">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <h2 class="section-title h3 mb-4">Un equipo comprometido con tu salud</h2>
            <p class="text-muted" style="font-size: 0.9rem">
              Cada profesional del Consultorio Integral Arenales comparte una misma filosofía: brindar atención
              cercana, escucha activa y excelencia médica en cada consulta.
            </p>
            <p class="text-muted" style="font-size: 0.9rem">
              Nuestro trabajo interdisciplinario nos permite ofrecer un abordaje integral para acompañarte con
              confianza y seguridad.
            </p>
            <a href="{{ route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-2">Conocé a nuestro equipo</a>
          </div>
          
          <div class="col-lg-7">
            <img
              src="/assets/images/midsection-doctors-with-arms-crossed.webp"
              alt="Equipo de profesionales médicos"
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
  <script src="{{ asset('assets/js/index.js') }}"></script>
@endpush
