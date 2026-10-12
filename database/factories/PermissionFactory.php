<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->slug(3);

        return [
            'name' => $name,
            'module' => Str::before($name, '.'),
            'description_en' => fake()->sentence(4),
            'description_sw' => fake()->sentence(4),
        ];
    }
}