<?php

namespace Database\Factories;

use App\Models\Skpd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skpd>
 */
class SkpdFactory extends Factory
{
    protected $model = Skpd::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_skpd' => fake()->company(),
            'website_url' => fake()->url(),
            'email' => fake()->companyEmail(),
            'kuota_bulanan' => fake()->numberBetween(1, 10),
            'status' => 'Active',
            'server_id' => null,
        ];
    }

    /**
     * Indicate that the SKPD is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
