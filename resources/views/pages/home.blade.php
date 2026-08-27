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
            <h1 class="display-6 mb-4">{{ $homePage->hero_title ?? 'Medicina especializada para acompañarte en cada etapa de tu vida.' }}</h1>
            <p class="mb-4">{{ $homePage->hero_description ?? 'En Consultorio Integral Arenales brindamos atención médica personalizada con un equipo de profesionales especializados.' }}</p>
            <div class="d-flex flex-column align-items-start gap-2">
              @if ($homePage->hero_button_1_text)
                <a href="{{ $homePage->hero_button_1_link ?? route('profesionales') }}" class="btn btn-brand">{{ $homePage->hero_button_1_text }}</a>
              @endif
              @if ($homePage->hero_button_2_text)
                <a href="{{ $homePage->hero_button_2_link ?? route('profesionales') }}" class="btn btn-brand">{{ $homePage->hero_button_2_text }}</a>
              @endif
            </div>
          </div>
          <div class="col-lg-7 hero-collage">
            <img
              class="img-a"
              src="{{ $homePage->hero_image_1 ? Storage::url($homePage->hero_image_1) : asset('assets/images/close-up-medicine-doctor-offering-helping-hand-handshake-partnership-trust-concept.webp') }}"
              alt=""
            />
            <img
              class="img-b"
              src="{{ $homePage->hero_image_2 ? Storage::url($homePage->hero_image_2) : asset('assets/images/doctor-performing-ultrasound-pregnant-woman.webp') }}"
              alt=""
            />
            <img
              class="img-c"
              src="{{ $homePage->hero_image_3 ? Storage::url($homePage->hero_image_3) : asset('assets/images/doctor-patient-discussing-something-while-sitting-table-medicine-health-care-concept.webp') }}"
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
              <div class="diferenciales-icon">
                <img class="" src="{{ $homePage->feature_1_icon ? Storage::url($homePage->feature_1_icon) : asset('assets/images/atencion-icon.png') }}" alt="" />
              </div>
              <h3>{{ $homePage->feature_1_title ?? 'Atención personalizada' }}</h3>
              <p>{{ $homePage->feature_1_description ?? 'Cada consulta comienza escuchando tus necesidades.' }}</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="diferenciales-icon">
                <img class="" src="{{ $homePage->feature_2_icon ? Storage::url($homePage->feature_2_icon) : asset('assets/images/profesionales-icon.webp') }}" alt="" />
              </div>
              <h3>{{ $homePage->feature_2_title ?? 'Profesionales especializados' }}</h3>
              <p>{{ $homePage->feature_2_description ?? 'Un equipo médico con amplia experiencia y formación continua.' }}</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="diferenciales-icon">
                <img class="" src="{{ $homePage->feature_3_icon ? Storage::url($homePage->feature_3_icon) : asset('assets/images/tecnologia-icon.webp') }}" alt="" />
              </div>
              <h3>{{ $homePage->feature_3_title ?? 'Tecnología de última generación' }}</h3>
              <p>{{ $homePage->feature_3_description ?? 'Equipamiento moderno para diagnósticos precisos y tratamientos de calidad.' }}</p>
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
            @foreach ($specialties as $specialty)
            <div class="swiper-slide h-auto">
              <div class="esp-card">
                <div class="esp-icon"><img class="" src="{{ $specialty->icon ? Storage::url($specialty->icon) : asset('assets/images/logo_300.png') }}" alt="{{ $specialty->name }}" /></div>
                <h4>{{ $specialty->name }}</h4>
                @if ($specialty->description)
                  <p>{{ $specialty->description }}</p>
                @else
                  <p>&nbsp;</p>
                @endif
                <a href="{{ route('especialidades', $specialty) }}" class="btn btn-brand btn-sm-pill mt-3 align-self-center">Solicitar Turno</a>
              </div>
            </div>
            @endforeach
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
            <h2 class="section-title h3 mb-4">{{ $homePage->team_title ?? 'Un equipo comprometido con tu salud' }}</h2>
            @if ($homePage->team_description)
              @foreach (explode("\n\n", $homePage->team_description) as $paragraph)
                <p class="text-muted" style="font-size: 0.9rem">{{ $paragraph }}</p>
              @endforeach
            @else
              <p class="text-muted" style="font-size: 0.9rem">
                Cada profesional del Consultorio Integral Arenales comparte una misma filosofía: brindar atención
                cercana, escucha activa y excelencia médica en cada consulta.
              </p>
              <p class="text-muted" style="font-size: 0.9rem">
                Nuestro trabajo interdisciplinario nos permite ofrecer un abordaje integral para acompañarte con
                confianza y seguridad.
              </p>
            @endif
            <a href="{{ $homePage->team_button_link ?? route('profesionales') }}" class="btn btn-brand btn-sm-pill mt-2">{{ $homePage->team_button_text ?? 'Conocé a nuestro equipo' }}</a>
          </div>
          
          <div class="col-lg-7">
            <img
              src="{{ $homePage->team_image ? Storage::url($homePage->team_image) : asset('assets/images/midsection-doctors-with-arms-crossed.webp') }}"
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
  <script src="{{ asset('assets/js/index.js') }}"></script>
@endpush
