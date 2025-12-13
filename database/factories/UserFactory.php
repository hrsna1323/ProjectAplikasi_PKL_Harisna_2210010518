<?php

namespace Database\Factories;

use App\Models\Skpd;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'Publisher',
            'skpd_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set the user role to Admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Admin',
            'skpd_id' => null,
        ]);
    }

    /**
     * Set the user role to Operator.
     */
    public function operator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Operator',
            'skpd_id' => null,
        ]);
    }

    /**
     * Set the user role to Publisher with an SKPD.
     */
    public function publisher(?Skpd $skpd = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Publisher',
            'skpd_id' => $skpd?->id ?? Skpd::factory(),
        ]);
    }
}
