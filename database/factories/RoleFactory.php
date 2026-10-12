<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->slug(2);

        return [
            'name' => $name,
            'display_name_en' => Str::headline($name),
            'display_name_sw' => Str::headline($name),
            'scope' => 'facility',
            'description_en' => fake()->sentence(4),
            'description_sw' => fake()->sentence(4),
            'requires_mfa' => false,
            'is_system' => false,
        ];
    }
}