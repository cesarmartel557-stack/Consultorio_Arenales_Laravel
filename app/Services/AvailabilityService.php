<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Horarios de un profesional para una fecha, marcando cuáles siguen libres.
     *
     * @param  Collection|null  $preloadedExceptions  Excepciones ya cargadas en memoria (opcional para optimizar consultas en lote).
     * @param  Collection|null  $preloadedSchedules  Horarios ya cargados en memoria (opcional).
     * @param  Collection|null  $preloadedTaken  Horarios ocupados en formato flipped [HH:mm => index] (opcional).
     * @return Collection<int, array{time: string, end: string, available: bool}>
     */
    public function slotsForDate(
        Doctor $doctor,
        Carbon $date,
        ?Collection $preloadedExceptions = null,
        ?Collection $preloadedSchedules = null,
        ?Collection $preloadedTaken = null
    ): Collection {
        $date = $date->copy()->startOfDay();

        if (! $this->isDateBookable($doctor, $date)) {
            return collect();
        }

        $exceptions = $preloadedExceptions ?? $doctor->scheduleExceptions()
            ->whereDate('date', $date->toDateString())
            ->get();

        if ($exceptions->contains(fn ($exception) => $exception->isFullDay())) {
            return collect();
        }

        $schedules = $preloadedSchedules
            ? $preloadedSchedules->where('is_active', true)->where('weekday', $date->dayOfWeek)->sortBy('start_time')->values()
            : ($doctor->relationLoaded('schedules')
                ? $doctor->schedules->where('is_active', true)->where('weekday', $date->dayOfWeek)->sortBy('start_time')->values()
                : $doctor->schedules()
                    ->where('is_active', true)
                    ->where('weekday', $date->dayOfWeek)
                    ->orderBy('start_time')
                    ->get());

        if ($schedules->isEmpty()) {
            return collect();
        }

        $taken = $preloadedTaken ?? Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date->toDateString())
            ->blocking()
            ->pluck('start_time')
            ->map(fn (string $time) => substr($time, 0, 5))
            ->flip();

        $earliest = now()->addHours($doctor->min_hours_notice);

        return $schedules
            ->flatMap(function ($schedule) use ($doctor, $date, $exceptions, $taken, $earliest) {
                $length = $schedule->slot_minutes ?: $doctor->slot_minutes;
                $cursor = $this->timeOn($date, $schedule->start_time);
                $end = $this->timeOn($date, $schedule->end_time);

                $slots = [];

                while ($cursor->copy()->addMinutes($length)->lte($end)) {
                    $slotEnd = $cursor->copy()->addMinutes($length);
                    $time = $cursor->format('H:i');

                    if (! $this->hitsException($exceptions, $date, $cursor, $slotEnd)) {
                        $slots[] = [
                            'time' => $time,
                            'end' => $slotEnd->format('H:i'),
                            'available' => ! $taken->has($time) && $cursor->gte($earliest),
                        ];
                    }

                    $cursor = $slotEnd;
                }

                return $slots;
            })
            ->unique('time')
            ->sortBy('time')
            ->values();
    }

    /**
     * Calendario completo de varias semanas en una sola consulta por lotes.
     *
     * @return array<int, array{index: int, label: string, days: array}>
     */
    public function calendar(Doctor $doctor, int $weeksCount = 8): array
    {
        $start = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = $start->copy()->addWeeks($weeksCount)->endOfWeek(Carbon::SATURDAY)->endOfDay();

        // 1. Cargar todas las excepciones del rango de varias semanas en 1 sola consulta
        $allExceptions = $doctor->scheduleExceptions()
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get()
            ->groupBy(fn ($exception) => Carbon::parse($exception->date)->toDateString());

        // 2. Cargar todos los horarios activos del médico
        $allSchedules = $doctor->relationLoaded('schedules')
            ? $doctor->schedules->where('is_active', true)
            : $doctor->schedules()->where('is_active', true)->get();

        // 3. Cargar todas las citas bloqueantes del rango en 1 sola consulta
        $allAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->blocking()
            ->get()
            ->groupBy(fn (Appointment $appointment) => Carbon::parse($appointment->date)->toDateString());

        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $diasCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $mesesCompletos = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        $weeks = [];

        for ($w = 0; $w < $weeksCount; $w++) {
            $weekStart = $start->copy()->addWeeks($w);
            $weekEnd = $weekStart->copy()->addDays(5);

            if ($weekStart->month === $weekEnd->month) {
                $weekLabel = $weekStart->day.' al '.$weekEnd->day.' de '.$meses[$weekEnd->month - 1].' '.$weekEnd->year;
            } else {
                $weekLabel = $weekStart->day.' de '.$meses[$weekStart->month - 1].' al '.$weekEnd->day.' de '.$meses[$weekEnd->month - 1].' '.$weekEnd->year;
            }

            $days = [];
            for ($d = 0; $d < 6; $d++) {
                $date = $weekStart->copy()->addDays($d);
                $dateKey = $date->toDateString();

                $dayExceptions = $allExceptions->get($dateKey, collect());
                $dayTaken = ($allAppointments->get($dateKey, collect()))
                    ->pluck('start_time')
                    ->map(fn (string $time) => substr($time, 0, 5))
                    ->flip();

                $slots = $this->slotsForDate(
                    $doctor,
                    $date,
                    $dayExceptions,
                    $allSchedules,
                    $dayTaken
                );

                $days[] = [
                    'date_string' => $dateKey,
                    'day_name' => $dias[$date->dayOfWeek],
                    'day_number' => $date->day,
                    'month_name' => $meses[$date->month - 1],
                    'full_label' => $diasCompletos[$date->dayOfWeek].' '.$date->day.' de '.$mesesCompletos[$date->month - 1],
                    'has_schedule' => $slots->isNotEmpty(),
                    'available_count' => $slots->where('available', true)->count(),
                    'slots' => $slots->values()->all(),
                ];
            }

            $weeks[] = [
                'index' => $w,
                'label' => $weekLabel,
                'days' => $days,
            ];
        }

        return $weeks;
    }

    /**
     * Semana de atención (lunes a sábado) con el estado de cada día y sus horarios, optimizada en una sola consulta.
     *
     * @return Collection<int, array{date: Carbon, date_string: string, has_schedule: bool, available_count: int, slots: array}>
     */
    public function week(Doctor $doctor, Carbon $weekStart): Collection
    {
        $start = $weekStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = $start->copy()->addDays(5)->endOfDay();

        // 1. Cargar todas las excepciones del rango semanal en una sola consulta
        $allExceptions = $doctor->scheduleExceptions()
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get()
            ->groupBy(fn ($exception) => Carbon::parse($exception->date)->toDateString());

        // 2. Cargar todos los horarios activos del médico (o reutilizar los precargados)
        $allSchedules = $doctor->relationLoaded('schedules')
            ? $doctor->schedules->where('is_active', true)
            : $doctor->schedules()->where('is_active', true)->get();

        // 3. Cargar todas las citas bloqueantes del rango semanal en una sola consulta
        $allAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->blocking()
            ->get()
            ->groupBy(fn (Appointment $appointment) => Carbon::parse($appointment->date)->toDateString());

        return collect(range(0, 5))->map(function (int $offset) use ($doctor, $start, $allExceptions, $allSchedules, $allAppointments) {
            $date = $start->copy()->addDays($offset);
            $dateKey = $date->toDateString();

            $dayExceptions = $allExceptions->get($dateKey, collect());
            $dayTaken = ($allAppointments->get($dateKey, collect()))
                ->pluck('start_time')
                ->map(fn (string $time) => substr($time, 0, 5))
                ->flip();

            $slots = $this->slotsForDate(
                $doctor,
                $date,
                $dayExceptions,
                $allSchedules,
                $dayTaken
            );

            $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            $diasCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $mesesCompletos = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

            return [
                'date' => $date,
                'date_string' => $dateKey,
                'day_name' => $dias[$date->dayOfWeek],
                'day_number' => $date->day,
                'month_name' => $meses[$date->month - 1],
                'full_label' => $diasCompletos[$date->dayOfWeek].' '.$date->day.' de '.$mesesCompletos[$date->month - 1],
                'has_schedule' => $slots->isNotEmpty(),
                'available_count' => $slots->where('available', true)->count(),
                'slots' => $slots->values()->all(),
            ];
        });
    }

    public function isSlotAvailable(Doctor $doctor, Carbon $date, string $time): bool
    {
        return $this->slotsForDate($doctor, $date)
            ->contains(fn (array $slot) => $slot['time'] === $time && $slot['available']);
    }

    public function slotLength(Doctor $doctor, Carbon $date, string $time): int
    {
        $schedules = $doctor->relationLoaded('schedules')
            ? $doctor->schedules->where('is_active', true)
            : $doctor->schedules()->where('is_active', true)->get();

        $schedule = $schedules
            ->where('weekday', $date->dayOfWeek)
            ->first(fn ($s) => $s->start_time <= $time && $s->end_time > $time);

        return $schedule?->slot_minutes ?: $doctor->slot_minutes;
    }

    private function isDateBookable(Doctor $doctor, Carbon $date): bool
    {
        $maxDays = ($doctor->max_days_ahead && $doctor->max_days_ahead > 0) ? $doctor->max_days_ahead : 60;

        return $date->gte(today()) && $date->lte(today()->addDays($maxDays));
    }

    private function timeOn(Carbon $date, string $time): Carbon
    {
        return $date->copy()->setTimeFromTimeString($time);
    }

    private function hitsException(Collection $exceptions, Carbon $date, Carbon $start, Carbon $end): bool
    {
        return $exceptions->contains(function ($exception) use ($date, $start, $end) {
            if ($exception->isFullDay()) {
                return true;
            }

            $blockStart = $this->timeOn($date, $exception->start_time ?? '00:00');
            $blockEnd = $this->timeOn($date, $exception->end_time ?? '23:59');

            return $start->lt($blockEnd) && $end->gt($blockStart);
        });
    }
}
