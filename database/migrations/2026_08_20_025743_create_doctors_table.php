<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('license')->nullable();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedSmallInteger('slot_minutes')->default(15);
            $table->unsignedSmallInteger('min_hours_notice')->default(2);
            $table->unsignedSmallInteger('max_days_ahead')->default(60);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('doctor_specialty', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->primary(['doctor_id', 'specialty_id']);
        });

        Schema::create('doctor_health_insurance', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_insurance_id')->constrained()->cascadeOnDelete();
            $table->primary(['doctor_id', 'health_insurance_id'], 'doctor_insurance_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_health_insurance');
        Schema::dropIfExists('doctor_specialty');
        Schema::dropIfExists('doctors');
    }
};
