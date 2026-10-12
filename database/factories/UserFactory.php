<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->optional()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'must_change_password' => false,
            'is_active' => true,
            'preferred_locale' => 'en',
            'password_changed_at' => now(),
            'primary_role_id' => Role::query()->where('name', 'opd_doctor')->value('id'),
        ];
    }

    public function username(string $username): static
    {
        return $this->state(fn (array $attributes) => ['username' => $username]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true, 'deactivated_at' => null]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);
    }

    /**
     * Attach a role and mark it primary.
     */
    public function withRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $role = Role::query()->where('name', $roleName)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching($role->getKey());
                $user->forceFill(['primary_role_id' => $role->getKey()])->saveQuietly();
                $user->forgetPermissionCache();
            }
        });
    }
}