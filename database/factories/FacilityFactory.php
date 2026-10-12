<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('RHH###'),
            'name' => fake()->company().' Referral Hospital',
            'short_name' => fake()->optional()->word(),
            'type' => 'referral_hospital',
            'hfr_code' => fake()->optional()->numerify('########'),
            'region' => fake()->randomElement(['Dar es Salaam', 'Dodoma', 'Mbeya', 'Mwanza', 'Arusha']),
            'district' => fake()->city(),
            'ward' => fake()->streetName(),
            'physical_address' => fake()->optional()->address(),
            'currency' => 'TZS',
            'timezone' => 'Africa/Dar_es_Salaam',
            'is_active' => true,
        ];
    }
}