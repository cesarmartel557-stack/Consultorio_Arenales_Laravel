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
     * @return Collection<int, array{time: string, end: string, available: bool}>
     */
    public function slotsForDate(Doctor $doctor, Carbon $date): Collection
    {
        $date = $date->copy()->startOfDay();

        if (! $this->isDateBookable($doctor, $date)) {
            return collect();
        }

        $exceptions = $doctor->scheduleExceptions()
            ->whereDate('date', $date)
            ->get();

        if ($exceptions->contains(fn ($exception) => $exception->isFullDay())) {
            return collect();
        }

        $schedules = $doctor->schedules()
            ->where('is_active', true)
            ->where('weekday', $date->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return collect();
        }

        $taken = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
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
     * Semana de atención (lunes a sábado) con el estado de cada día.
     *
     * @return Collection<int, array{date: Carbon, has_schedule: bool, available_count: int}>
     */
    public function week(Doctor $doctor, Carbon $weekStart): Collection
    {
        $start = $weekStart->copy()->startOfWeek(Carbon::MONDAY);

        return collect(range(0, 5))->map(function (int $offset) use ($doctor, $start) {
            $date = $start->copy()->addDays($offset);
            $slots = $this->slotsForDate($doctor, $date);

            return [
                'date' => $date,
                'has_schedule' => $slots->isNotEmpty(),
                'available_count' => $slots->where('available', true)->count(),
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
        $schedule = $doctor->schedules()
            ->where('is_active', true)
            ->where('weekday', $date->dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->first();

        return $schedule?->slot_minutes ?: $doctor->slot_minutes;
    }

    private function isDateBookable(Doctor $doctor, Carbon $date): bool
    {
        return $date->gte(today()) && $date->lte(today()->addDays($doctor->max_days_ahead));
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
