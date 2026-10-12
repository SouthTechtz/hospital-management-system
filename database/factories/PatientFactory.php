<?php

namespace Database\Factories;

use App\Enums\PayerType;
use App\Enums\Sex;
use App\Models\Facility;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $sex = fake()->randomElement([Sex::Male, Sex::Female]);

        return [
            'facility_id' => Facility::query()->value('id') ?? Facility::factory(),
            'first_name' => fake()->firstName($sex === Sex::Male ? 'male' : 'female'),
            'middle_name' => fake()->optional(0.3)->firstName($sex === Sex::Male ? 'male' : 'female'),
            'last_name' => fake()->lastName(),
            'sex' => $sex->value,
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 day')->format('Y-m-d'),
            'birth_date_estimated' => false,
            'national_id' => fake()->optional(0.6)->numerify('#############'),
            'phone' => fake()->optional(0.8)->e164PhoneNumber(),
            'email' => fake()->optional(0.3)->safeEmail(),
            'region' => fake()->optional()->randomElement(['Dar es Salaam', 'Dodoma', 'Mbeya', 'Mwanza', 'Arusha']),
            'district' => fake()->optional()->city(),
            'ward' => fake()->optional()->streetName(),
            'nationality' => 'Tanzanian',
            'payer_type' => PayerType::Cash->value,
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'patient_category' => 'general',
            'registered_by' => null,
            'registered_at' => now(),
        ];
    }

    public function withMrn(string $mrn): static
    {
        return $this->state(fn (array $attributes) => ['mrn' => $mrn]);
    }

    public function deceased(): static
    {
        return $this->state(fn (array $attributes) => [
            'deceased_at' => now()->subDays(fake()->numberBetween(1, 900)),
        ]);
    }
}