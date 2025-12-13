<?php

namespace Database\Seeders;

use App\Models\KategoriKonten;
use Illuminate\Database\Seeder;

class KategoriKontenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'Berita',
                'deskripsi' => 'Berita terkini dan informasi aktual dari SKPD',
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Pengumuman',
                'deskripsi' => 'Pengumuman resmi dan informasi penting untuk publik',
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Artikel',
                'deskripsi' => 'Artikel informatif dan edukatif dari SKPD',
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Kegiatan',
                'deskripsi' => 'Dokumentasi kegiatan dan acara SKPD',
                'is_active' => true,
            ],
            [
                'nama_kategori' => 'Layanan Publik',
                'deskripsi' => 'Informasi layanan publik yang disediakan SKPD',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            KategoriKonten::firstOrCreate(
                ['nama_kategori' => $category['nama_kategori']],
                $category
            );
        }
    }
}
