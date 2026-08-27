@php
  use Illuminate\Support\Carbon;
  $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
@endphp

<div>
  @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/turnos.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/turnos-datos.css') }}" />
  @endpush

  <header class="page-band">
    <div class="container"><h1>Turnos</h1></div>
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
            {{-- Profesional --}}
            <div class="row g-4 align-items-start">
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

                @php
                  $displayInsurances = $doctor->healthInsurances->filter(function ($insurance) {
                      return strtolower(trim($insurance->name)) !== 'particular';
                  });
                @endphp
                @if ($displayInsurances->isNotEmpty())
                  <div class="d-flex flex-wrap align-items-center gap-2 doc-os mb-3">
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

                <div class="d-flex flex-wrap gap-2 doc-hours">
                  @foreach ($doctor->schedules as $schedule)
                    <div class="chip"><b>{{ $schedule->weekday_name }}</b>{{ substr($schedule->start_time, 0, 5) }} a {{ substr($schedule->end_time, 0, 5) }}</div>
                  @endforeach
                </div>
              </div>
            </div>

            <div class="booking-sep"></div>

            @error('form')
              <div class="alert alert-danger" role="alert">{{ $message }}</div>
            @enderror

            @php
              $currentDay = $this->week->firstWhere('date_string', $selectedDate)
                  ?? $this->week->firstWhere('has_schedule', true)
                  ?? $this->week->first();
              $currentSlots = collect($currentDay['slots'] ?? []);
              $mananaSlots = $currentSlots->filter(fn ($s) => (int) substr($s['time'], 0, 2) < 13);
              $tardeSlots = $currentSlots->filter(fn ($s) => (int) substr($s['time'], 0, 2) >= 13);
            @endphp

            <div>
              {{-- PASO 1: día y horario --}}
              @if ($step === 1)
                <div>
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                      <div class="booking-title">Elegí día y horario</div>
                      <div class="booking-sub">Tocá un horario disponible para reservar</div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                      <div class="stepper" aria-label="Paso 1 de 2">
                        <div class="st is-current"><span class="dot">1</span><span class="txt">Turno</span></div>
                        <span class="bar"></span>
                        <div class="st"><span class="dot">2</span><span class="txt">Tus datos</span></div>
                      </div>
                      <div class="d-flex align-items-center gap-2 week-nav">
                        <button type="button" wire:click="previousWeek" @disabled($weekOffset <= 0) aria-label="Semana anterior">
                          <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="week-label">{{ $this->weekLabel() }}</span>
                        <button type="button" wire:click="nextWeek" @disabled($weekOffset >= 8) aria-label="Semana siguiente">
                          <i class="bi bi-chevron-right"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="days-scroll">
                    @foreach ($this->week as $day)
                      <button
                        type="button"
                        wire:click="selectDate('{{ $day['date_string'] }}')"
                        @disabled(! $day['has_schedule'])
                        class="day-pill @if(! $day['has_schedule']) is-off @endif @if($selectedDate === $day['date_string']) is-active @endif"
                      >
                        <small>{{ $day['day_name'] }}</small>
                        <b>{{ $day['day_number'] }}</b>
                        <i>{{ $day['month_name'] }}</i>
                        @if ($day['available_count'] > 0)
                          <span class="day-dot"></span>
                        @endif
                      </button>
                    @endforeach
                  </div>

                  <div>
                    @if ($currentSlots->isEmpty())
                      <div class="no-slots">El profesional no atiende este día. Probá con otro.</div>
                    @else
                      <div>
                        @if ($mananaSlots->isNotEmpty())
                          <div>
                            <div class="slot-group-title">Mañana</div>
                            <div class="slots">
                              @foreach ($mananaSlots as $slot)
                                <button
                                  type="button"
                                  wire:click="selectTime('{{ $slot['time'] }}')"
                                  @disabled(! $slot['available'])
                                  title="{{ $slot['available'] ? '' : 'No disponible' }}"
                                  class="slot @if($selectedTime === $slot['time']) is-selected @endif"
                                >
                                  {{ $slot['time'] }}
                                </button>
                              @endforeach
                            </div>
                          </div>
                        @endif

                        @if ($tardeSlots->isNotEmpty())
                          <div class="mt-3">
                            <div class="slot-group-title">Tarde</div>
                            <div class="slots">
                              @foreach ($tardeSlots as $slot)
                                <button
                                  type="button"
                                  wire:click="selectTime('{{ $slot['time'] }}')"
                                  @disabled(! $slot['available'])
                                  title="{{ $slot['available'] ? '' : 'No disponible' }}"
                                  class="slot @if($selectedTime === $slot['time']) is-selected @endif"
                                >
                                  {{ $slot['time'] }}
                                </button>
                              @endforeach
                            </div>
                          </div>
                        @endif
                      </div>
                    @endif
                  </div>

                  <div class="confirm-bar">
                    <div class="sel">
                      @if ($selectedDate && $selectedTime && $currentDay)
                        <div>Turno seleccionado: <b>{{ $currentDay['full_label'] }} · {{ $selectedTime }} hs</b></div>
                      @else
                        <div>Ningún horario seleccionado<b>—</b></div>
                      @endif
                    </div>
                    <button
                      type="button"
                      class="btn btn-brand"
                      wire:click="goToDetails"
                      @disabled(! $selectedDate || ! $selectedTime)
                    >
                      Pedir Turno
                    </button>
                  </div>
                </div>
              @endif

              {{-- PASO 2: datos del paciente --}}
              @if ($step === 2)
                <div>
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                      <div class="booking-title">Completá tus datos y listo</div>
                      <div class="booking-sub">Te confirmamos el turno por mail</div>
                    </div>
                    <div class="stepper" aria-label="Paso 2 de 2">
                      <div class="st is-done"><span class="dot"><i class="bi bi-check-lg"></i></span><span class="txt">Turno</span></div>
                      <span class="bar is-done"></span>
                      <div class="st is-current"><span class="dot">2</span><span class="txt">Tus datos</span></div>
                    </div>
                  </div>

                  <div class="slot-summary mb-4">
                    <div class="d-flex align-items-center gap-3">
                      <div class="ico"><i class="bi bi-calendar2-check"></i></div>
                      <div>
                        <small>Turno seleccionado</small>
                        <b>{{ ($currentDay['full_label'] ?? '') }} · {{ $selectedTime }} hs</b>
                      </div>
                    </div>
                    <button type="button" class="btn btn-brand btn-sm-pill" wire:click="backToSchedule">Cambiar</button>
                  </div>

                <form wire:submit="submit" novalidate>
                  <div style="position:absolute; left:-9999px;" aria-hidden="true">
                    <label for="website">No completar</label>
                    <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off" />
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="field">
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="nombre" placeholder=" " autocomplete="given-name" maxlength="60" wire:model="first_name" />
                        <label for="nombre">Nombre</label>
                        <i class="bi bi-person fi"></i>
                        @error('first_name') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="field">
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="apellido" placeholder=" " autocomplete="family-name" maxlength="60" wire:model="last_name" />
                        <label for="apellido">Apellido</label>
                        <i class="bi bi-person-badge fi"></i>
                        @error('last_name') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="field">
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="telefono" placeholder=" " autocomplete="tel" maxlength="20" wire:model="phone" />
                        <label for="telefono">Teléfono</label>
                        <i class="bi bi-telephone fi"></i>
                        @error('phone') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="field">
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="mail" placeholder=" " autocomplete="email" maxlength="120" wire:model="email" />
                        <label for="mail">Mail</label>
                        <i class="bi bi-envelope fi"></i>
                        @error('email') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="field">
                        <input type="text" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" class="form-control @error('dni') is-invalid @enderror" id="dni" placeholder=" " autocomplete="off" wire:model="dni" title="8 dígitos sin puntos ni guiones" />
                        <label for="dni">DNI</label>
                        <i class="bi bi-credit-card-2-front fi"></i>
                        @error('dni') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="field">
                        <select
                          class="form-select {{ filled($health_insurance_id) ? 'has-value' : '' }} @error('health_insurance_id') is-invalid @enderror"
                          id="obra"
                          wire:model="health_insurance_id"
                        >
                          <option value="">Seleccionar</option>
                          <option value="particular">Particular (sin obra social)</option>
                          @foreach ($doctor->healthInsurances as $insurance)
                            @if(strtolower(trim($insurance->name)) !== 'particular')
                              <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
                            @endif
                          @endforeach
                        </select>
                        <label for="obra">Obra Social / Particular</label>
                        <i class="bi bi-shield-plus fi"></i>
                        @error('health_insurance_id') <div class="invalid-msg d-block">{{ $message }}</div> @enderror
                      </div>
                    </div>
                  </div>

                  <p class="form-hint mb-0">
                    <i class="bi bi-lock me-1"></i>Usamos tus datos únicamente para gestionar el turno.
                  </p>

                  <div class="submit-bar d-flex justify-content-end">
                    <button type="submit" class="btn btn-brand btn-lg-pill" wire:loading.attr="disabled">
                      <span wire:loading.remove wire:target="submit">Enviar</span>
                      <span wire:loading wire:target="submit">Enviando…</span>
                    </button>
                  </div>
                </form>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
