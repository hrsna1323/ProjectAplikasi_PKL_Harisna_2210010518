<?php

use App\Models\ActivityLog;
use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 8: Content default status
 * 
 * *For any* content created by Publisher, the initial status must be "Pending".
 * 
 * **Validates: Requirements 3.2**
 */
describe('Property 8: Content default status', function () {
    
    beforeEach(function () {
        $this->contentService = app(ContentService::class);
    });

    /**
     * Property test: For any content created via ContentService::createContent(),
     * the status must always be "Pending" regardless of input data.
     */
    test('content created via ContentService always has Pending status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content data with various status values (should be overridden)
            $randomStatuses = [
                Content::STATUS_DRAFT,
                Content::STATUS_PENDING,
                Content::STATUS_APPROVED,
                Content::STATUS_REJECTED,
                Content::STATUS_PUBLISHED,
                null,
                '',
                'InvalidStatus',
            ];
            
            $inputStatus = fake()->randomElement($randomStatuses);
            
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];
            
            // Randomly include status in input data (should be overridden)
            if ($inputStatus !== null) {
                $contentData['status'] = $inputStatus;
            }
            
            // Create content via service
            $content = $this->contentService->createContent($contentData, $publisher);
            
            // Verify status is always "Pending"
            expect($content->status)->toBe(Content::STATUS_PENDING,
                "Content created via ContentService should always have 'Pending' status, but got '{$content->status}'. Input status was: " . ($inputStatus ?? 'null'));
            
            // Verify content is persisted with correct status
            $persistedContent = Content::find($content->id);
            expect($persistedContent->status)->toBe(Content::STATUS_PENDING,
                "Persisted content should have 'Pending' status");
            
            // Clean up for next iteration
            Content::where('id', $content->id)->delete();
            User::where('id', $publisher->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content created directly via model without explicit status,
     * the default status should be "Pending".
     */
    test('content model default status is Pending', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create content without specifying status
            $content = Content::create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
                // Note: status is NOT specified
            ]);
            
            // Verify default status is "Pending"
            expect($content->status)->toBe(Content::STATUS_PENDING,
                "Content created without explicit status should default to 'Pending'");
            
            // Verify persisted content has correct status
            $persistedContent = Content::find($content->id);
            expect($persistedContent->status)->toBe(Content::STATUS_PENDING,
                "Persisted content should have 'Pending' status");
            
            // Clean up for next iteration
            Content::where('id', $content->id)->delete();
            User::where('id', $publisher->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content created via factory without state modifier,
     * the default status should be "Pending".
     */
    test('content factory default status is Pending', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create content via factory without any state modifier
            $content = Content::factory()->create();
            
            // Verify default status is "Pending"
            expect($content->status)->toBe(Content::STATUS_PENDING,
                "Content created via factory should default to 'Pending' status");
            
            // Clean up for next iteration
            $skpdId = $content->skpd_id;
            $publisherId = $content->publisher_id;
            $kategoriId = $content->kategori_id;
            
            Content::where('id', $content->id)->delete();
            User::where('id', $publisherId)->delete();
            Skpd::where('id', $skpdId)->delete();
            KategoriKonten::where('id', $kategoriId)->delete();
        }
    });
});
