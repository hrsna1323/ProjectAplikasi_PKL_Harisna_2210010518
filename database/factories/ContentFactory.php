<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'skpd_id' => Skpd::factory(),
            'publisher_id' => User::factory(),
            'judul' => fake()->sentence(),
            'deskripsi' => fake()->paragraph(),
            'kategori_id' => KategoriKonten::factory(),
            'url_publikasi' => fake()->url(),
            'tanggal_publikasi' => fake()->date(),
            'status' => Content::STATUS_PENDING,
        ];
    }

    /**
     * Set the content status to Draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Content::STATUS_DRAFT,
        ]);
    }

    /**
     * Set the content status to Pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Content::STATUS_PENDING,
        ]);
    }

    /**
     * Set the content status to Approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Content::STATUS_APPROVED,
        ]);
    }

    /**
     * Set the content status to Rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Content::STATUS_REJECTED,
        ]);
    }

    /**
     * Set the content status to Published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Content::STATUS_PUBLISHED,
        ]);
    }
}
