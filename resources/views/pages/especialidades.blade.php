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

    <!-- Detalle: Ginecología -->
    <section class="py-5" id="ginecologia">
      <div class="container py-3">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <img
              class="esp-photo"
              src="/assets/images/midsection-doctors-with-arms-crossed-2.webp"
              alt="Profesionales médicos del consultorio"
            />
          </div>
          <div class="col-lg-6 offset-lg-1 esp-detail">
            <div class="esp-round-icon"><img src="/assets/images/icon-gineco.webp" alt="Ginecología" /></div>
            <h2 class="mb-3">Ginecología</h2>
            <p class="mb-0">
              Prevención, diagnóstico y tratamiento de enfermedades ginecológicas. Realizamos controles
              periódicos y acompañamos cada etapa de la salud femenina.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Profesionales de Ginecología -->
    <section class="py-5">
      <div class="container">
        <h3 class="mb-5 text-center">Profesionales en Ginecología</h3>
        <div class="doc-grid" id="grid">

          <!-- Dr. Gabriel Toledo -->
          <div class="doc-item" data-aos="fade-up">
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="/assets/images/gabriel-toledo.webp" alt="Dr. Gabriel Toledo" />
                </div>
                <div class="col">
                  <h3>Dr. Gabriel Toledo</h3>
                  <div class="doc-mn">MN 92785 &nbsp;|&nbsp; MP 446896</div>
                  <p class="doc-role">Especialista en Ginecología, Obstetricia y Patología mamaria</p>
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                      <span class="doc-os-label me-1">Obras Sociales</span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/sancor-salud.svg" alt="Sancor Salud" /></span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/osde-logo.webp" alt="OSDE" /></span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/omint.svg" alt="Omint" /></span>
                  </div>
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    <div class="chip"><b>Martes</b>14:00 a 19:00</div>
                    <div class="chip"><b>Jueves</b>14:00 a 19:00</div>
                    <div class="chip"><b>Viernes</b>9:00 a 13:00</div>
                  </div>
                  <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Dr. Humberto Giambastiani -->
          <div class="doc-item" data-aos="fade-up" data-aos-delay="200">
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="/assets/images/humberto-giambastiani.webp" alt="Dr. Humberto Giambastiani" />
                </div>
                <div class="col">
                  <h3>Dr. Humberto Giambastiani</h3>
                  <div class="doc-mn">MN 43029</div>
                  <p class="doc-role">Ginecología y Obstetricia</p>
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                    <span class="doc-os-label me-1">Obras Sociales</span>
                     <span class="obra-soc"><img src="/assets/images/prepagas/logo-vector-galeno.webp" alt="Galeno" /></span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/osde-logo.webp" alt="OSDE" /></span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/medicus.svg" alt="Medicus" /></span>
                  </div>
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    <div class="chip"><b>Martes</b>9:00 a 13:00</div>
                    <div class="chip"><b>Viernes</b>15:00 a 19:00</div>
                  </div>
                  <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Dr. Mariano Martinotti -->
          <div class="doc-item" data-aos="fade-up">
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="/assets/images/mariano-martinotti.webp" alt="Dr. Mariano Martinotti" />
                </div>
                <div class="col">
                  <h3>Dr. Mariano Martinotti</h3>
                  <div class="doc-mn">MN 111797</div>
                  <p class="doc-role">Tocoginecólogo</p>
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                    <span class="doc-os-label me-1">Obras Sociales</span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/hospital-aleman.svg" alt="Hospital Aleman" /></span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/medife.svg" alt="Medife" /></span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/swiss-medical.svg" alt="Swiss Medical" /></span>
                  </div>
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    <div class="chip"><b>Miércoles</b>9:00 a 14:00</div>
                  </div>
                  <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Lic. Carla Ferreyra -->
          <div class="doc-item" data-aos="fade-up" data-aos-delay="100">
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="/assets/images/silvina-vulcano.webp" alt="Dra. Silvina Vulcano" />
                </div>
                <div class="col">
                  <h3>Dra. Silvina Vulcano</h3>
                  <div class="doc-mn">MN 8321</div>
                  <p class="doc-role">Especialista en Ginecología y Cannabis medicinal.</p>
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                    <span class="doc-os-label me-1">Obras Sociales</span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/hospital-britanico.svg" alt="Hospital Britanico" /></span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/federada-salud.svg" alt="Federada" /></span>
                  </div>
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    <div class="chip"><b>Lunes</b>10:00 a 16:00</div>
                    <div class="chip"><b>Jueves</b>9:00 a 13:00</div>
                  </div>
                  <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Dra. Laura Bidegain -->
          <div class="doc-item" data-aos="fade-up" data-aos-delay="200">
            <div class="doc-card">
              <div class="row g-4 align-items-center">
                <div class="col-auto">
                  <img class="doc-photo" src="/assets/images/claudia-krasnapolsky.webp" alt="Dra. Laura Bidegain" />
                </div>
                <div class="col">
                  <h3>Dra. Claudia Krasnapolsky</h3>
                  <div class="doc-mn">MN 105432</div>
                  <p class="doc-role">Ginecología, Adolescencia y Medicina Est&acute;tica</p>
                  <div class="d-flex flex-wrap gap-2 doc-os mb-3">
                     <span class="doc-os-label me-1">Obras Sociales</span>
                      <span class="obra-soc"><img src="/assets/images/prepagas/prevencion-salud.svg" alt="" /></span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/omint.svg" alt="" /></span>
                    <span class="obra-soc"><img src="/assets/images/prepagas/medife.svg" alt="" /></span>
                  </div>
                  <div class="d-flex flex-wrap gap-2 doc-hours mb-3">
                    <div class="chip"><b>Miércoles</b>14:00 a 19:00</div>
                  </div>
                  <a href="{{ route('profesionales') }}" class="btn btn-brand">Solicitar Turno</a>
                </div>
              </div>
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
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="{{ asset('assets/js/especialidades.js') }}"></script>
@endpush
