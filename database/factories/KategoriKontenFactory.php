<?php

namespace Database\Factories;

use App\Models\KategoriKonten;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KategoriKonten>
 */
class KategoriKontenFactory extends Factory
{
    protected $model = KategoriKonten::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => fake()->randomElement(['Berita', 'Pengumuman', 'Artikel', 'Kegiatan', 'Layanan Publik']),
            'deskripsi' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
