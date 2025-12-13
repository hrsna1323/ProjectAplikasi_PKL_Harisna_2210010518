<?php

use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 7: Content required fields validation
 * 
 * *For any* content submission attempt, if any required field (judul, deskripsi, 
 * kategori, url_publikasi, tanggal_publikasi) is missing, the submission should be rejected.
 * 
 * **Validates: Requirements 3.1**
 */
describe('Property 7: Content required fields validation', function () {

    beforeEach(function () {
        // Create SKPD
        $this->skpd = Skpd::factory()->create(['status' => 'Active']);
        
        // Create publisher for this SKPD
        $this->publisher = User::factory()->create([
            'role' => 'Publisher',
            'skpd_id' => $this->skpd->id,
        ]);
        
        // Create category
        $this->kategori = KategoriKonten::factory()->create(['is_active' => true]);
    });

    /**
     * Property test: For any content submission with missing judul,
     * the submission should be rejected with validation error.
     */
    test('content submission without judul is rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                // 'judul' is intentionally missing
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $this->kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            $response->assertSessionHasErrors('judul');
        }
    });

    /**
     * Property test: For any content submission with missing deskripsi,
     * the submission should be rejected with validation error.
     */
    test('content submission without deskripsi is rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                'judul' => fake()->sentence(),
                // 'deskripsi' is intentionally missing
                'kategori_id' => $this->kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            $response->assertSessionHasErrors('deskripsi');
        }
    });

    /**
     * Property test: For any content submission with missing kategori_id,
     * the submission should be rejected with validation error.
     */
    test('content submission without kategori_id is rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                // 'kategori_id' is intentionally missing
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            $response->assertSessionHasErrors('kategori_id');
        }
    });

    /**
     * Property test: For any content submission with missing url_publikasi,
     * the submission should be rejected with validation error.
     */
    test('content submission without url_publikasi is rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $this->kategori->id,
                // 'url_publikasi' is intentionally missing
                'tanggal_publikasi' => fake()->date(),
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            $response->assertSessionHasErrors('url_publikasi');
        }
    });

    /**
     * Property test: For any content submission with missing tanggal_publikasi,
     * the submission should be rejected with validation error.
     */
    test('content submission without tanggal_publikasi is rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $this->kategori->id,
                'url_publikasi' => fake()->url(),
                // 'tanggal_publikasi' is intentionally missing
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            $response->assertSessionHasErrors('tanggal_publikasi');
        }
    });

    /**
     * Property test: For any content submission with randomly missing required fields,
     * the submission should be rejected with validation errors for all missing fields.
     */
    test('content submission with randomly missing fields is rejected', function () {
        $requiredFields = ['judul', 'deskripsi', 'kategori_id', 'url_publikasi', 'tanggal_publikasi'];
        
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Randomly select which fields to omit (at least one)
            $fieldsToOmit = fake()->randomElements(
                $requiredFields,
                fake()->numberBetween(1, count($requiredFields))
            );
            
            // Build content data with some fields missing
            $contentData = [];
            
            if (!in_array('judul', $fieldsToOmit)) {
                $contentData['judul'] = fake()->sentence();
            }
            if (!in_array('deskripsi', $fieldsToOmit)) {
                $contentData['deskripsi'] = fake()->paragraph();
            }
            if (!in_array('kategori_id', $fieldsToOmit)) {
                $contentData['kategori_id'] = $this->kategori->id;
            }
            if (!in_array('url_publikasi', $fieldsToOmit)) {
                $contentData['url_publikasi'] = fake()->url();
            }
            if (!in_array('tanggal_publikasi', $fieldsToOmit)) {
                $contentData['tanggal_publikasi'] = fake()->date();
            }

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            // Verify that validation errors exist for all omitted fields
            foreach ($fieldsToOmit as $field) {
                $response->assertSessionHasErrors($field,
                    "Missing field '{$field}' should trigger validation error. Omitted fields: " . implode(', ', $fieldsToOmit)
                );
            }
        }
    });

    /**
     * Property test: For any content submission with all required fields present and valid,
     * the submission should be accepted (no validation errors for required fields).
     */
    test('content submission with all required fields is accepted', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $this->kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];

            $response = $this->actingAs($this->publisher)
                ->post(route('publisher.content.store'), $contentData);

            // Should not have validation errors for required fields
            $response->assertSessionDoesntHaveErrors(['judul', 'deskripsi', 'kategori_id', 'url_publikasi', 'tanggal_publikasi']);
            
            // Should redirect (successful submission)
            $response->assertRedirect();
        }
    });
});
