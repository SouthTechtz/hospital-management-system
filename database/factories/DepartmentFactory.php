<?php

namespace Database\Factories;

use App\Enums\Directorate;
use App\Models\Department;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'facility_id' => Facility::query()->value('id') ?? Facility::factory(),
            'code' => fake()->unique()->bothify('DEPT###'),
            'name' => fake()->randomElement(['Outpatient Department', 'Emergency Department', 'Paediatric Ward', 'Adult Medical Ward', 'Maternity']),
            'directorate' => fake()->randomElement(Directorate::cases())->value,
            'location' => fake()->optional()->word(),
            'is_clinical' => true,
            'is_active' => true,
        ];
    }
}