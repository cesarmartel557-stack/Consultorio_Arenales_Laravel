<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->foreignId('specialty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('health_insurance_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('status')->default('pending');
            $table->string('cancelled_by')->nullable();
            $table->text('status_reason')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->text('notes')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Bloquea a nivel de base de datos que dos pacientes ocupen el mismo slot.
            // Queda NULL cuando el turno se cancela/rechaza, liberando el horario
            // (MySQL permite múltiples NULL en un índice único).
            $storedExpression = DB::getDriverName() === 'sqlite'
                ? "CASE WHEN status IN ('pending', 'confirmed') THEN (doctor_id || '|' || date || '|' || start_time) ELSE NULL END"
                : "CASE WHEN status IN ('pending', 'confirmed') THEN CONCAT(doctor_id, '|', `date`, '|', start_time) ELSE NULL END";

            $table->string('active_slot', 64)
                ->nullable()
                ->storedAs($storedExpression);

            $table->index(['doctor_id', 'date']);
            $table->index('email');
            $table->index('status');
            $table->unique('active_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
