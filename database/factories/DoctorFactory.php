<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'title' => 'Dr.',
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'license' => 'MN '.fake()->numberBetween(10000, 99999),
            'headline' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'slot_minutes' => 15,
            'min_hours_notice' => 2,
            'max_days_ahead' => 60,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
