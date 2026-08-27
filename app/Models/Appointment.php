<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'specialty_id',
        'health_insurance_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'cancelled_by',
        'status_reason',
        'first_name',
        'last_name',
        'email',
        'phone',
        'dni',
        'notes',
        'confirmed_at',
        'cancelled_at',
        'reminder_sent_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AppointmentStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $appointment) {
            $appointment->uuid ??= (string) Str::uuid();
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function healthInsurance(): BelongsTo
    {
        return $this->belongsTo(HealthInsurance::class);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', array_column(AppointmentStatus::blocking(), 'value'));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('date', '>=', today());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getPatientNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStartsAtAttribute(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.$this->start_time);
    }

    public function isPast(): bool
    {
        return $this->starts_at->isPast();
    }

    /**
     * El paciente sólo puede gestionar turnos que siguen vigentes y no ocurrieron aún.
     */
    public function isManageableByPatient(): bool
    {
        return $this->status->blocksSlot() && ! $this->isPast();
    }
}
