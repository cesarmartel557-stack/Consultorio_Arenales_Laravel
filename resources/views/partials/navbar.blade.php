@php($navSpecialties = \App\Models\Specialty::where('is_active', true)->orderBy('sort_order')->get())

<nav class="navbar py-3 sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
      <img class="logo" width="240" src="{{ asset('assets/images/logo_orig_svg.svg') }}" alt="Consultorio Integral Arenales" />
    </a>

    <ul class="navbar-nav nav-desktop mb-0">
      <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown">Especialidades</a>
        <ul class="dropdown-menu">
          @foreach ($navSpecialties as $specialty)
            <li><a class="dropdown-item" href="{{ route('especialidades', ['specialty' => $specialty->slug]) }}">{{ $specialty->name }}</a></li>
          @endforeach
        </ul>
      </li>
      <li class="nav-item"><a class="nav-link" href="{{ route('profesionales') }}">Profesionales</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('nosotros') }}">Nosotros</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('contacto') }}">Contacto</a></li>
      <li class="nav-item"><a class="btn btn-nav" href="{{ route('profesionales') }}">Solicitar Turno</a></li>
    </ul>

    <button class="burger" id="burgerBtn" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-controls="navMenu" aria-label="Abrir menú">
      <span class="burger-inner"></span>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-end offcanvas-brand" tabindex="-1" id="navMenu" aria-labelledby="navMenuTitle">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title brand-mark d-flex align-items-center gap-2" id="navMenuTitle">
      <img class="logo" width="200" src="{{ asset('assets/images/logo_orig_svg.svg') }}" alt="Consultorio Integral Arenales" />
    </h5>
    <button class="burger is-open" type="button" data-bs-dismiss="offcanvas" aria-label="Cerrar menú">
      <span class="burger-inner"></span>
    </button>
  </div>
  <div class="offcanvas-body">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Inicio</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#!" role="button" data-bs-toggle="dropdown" data-bs-display="static">Especialidades</a>
        <ul class="dropdown-menu">
          @foreach ($navSpecialties as $specialty)
            <li><a class="dropdown-item" href="{{ route('especialidades', ['specialty' => $specialty->slug]) }}">{{ $specialty->name }}</a></li>
          @endforeach
        </ul>
      </li>
      <li class="nav-item"><a class="nav-link" href="{{ route('profesionales') }}">Profesionales</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('nosotros') }}">Nosotros</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('contacto') }}">Contacto</a></li>
    </ul>
    <a class="btn btn-light btn-sm-pill fw-semibold text-brand px-3 py-2 mt-4" href="{{ route('profesionales') }}">Solicitar Turno</a>
  </div>
</div>
