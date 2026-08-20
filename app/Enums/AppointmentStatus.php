<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Confirmed => 'Confirmado',
            self::Rejected => 'Rechazado',
            self::Cancelled => 'Cancelado',
            self::Completed => 'Atendido',
            self::NoShow => 'No asistió',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'success',
            self::Rejected, self::Cancelled => 'danger',
            self::Completed => 'info',
            self::NoShow => 'gray',
        };
    }

    /**
     * Estados que ocupan el horario en la agenda.
     */
    public static function blocking(): array
    {
        return [self::Pending, self::Confirmed];
    }

    public function blocksSlot(): bool
    {
        return in_array($this, self::blocking(), true);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
