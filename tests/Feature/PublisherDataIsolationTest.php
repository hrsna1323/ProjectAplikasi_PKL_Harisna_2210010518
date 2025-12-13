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
 * Feature: manajemen-konten-skpd, Property 10: Data isolation for Publishers
 * 
 * *For any* Publisher user, when viewing content list, only content from their 
 * associated SKPD should be visible.
 * 
 * **Validates: Requirements 3.5**
 */
describe('Property 10: Data isolation for Publishers', function () {
    
    beforeEach(function () {
        $this->contentService = app(ContentService::class);
    });

    /**
     * Property test: For any Publisher user, getContentByPublisher() should return
     * only content from their associated SKPD, regardless of how many other SKPDs
     * and their content exist in the system.
     */
    test('publisher can only see content from their own SKPD', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create random number of SKPDs (2-5)
            $numSkpds = fake()->numberBetween(2, 5);
            $skpds = [];
            $publishers = [];
            
            for ($j = 0; $j < $numSkpds; $j++) {
                $skpd = Skpd::factory()->create(['status' => 'Active']);
                $skpds[] = $skpd;
                
                // Create a publisher for each SKPD
                $publisher = User::factory()->create([
                    'role' => 'Publisher',
                    'skpd_id' => $skpd->id,
                ]);
                $publishers[] = $publisher;
            }
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create random content for each SKPD (1-5 content per SKPD)
            $contentPerSkpd = [];
            foreach ($skpds as $index => $skpd) {
                $numContent = fake()->numberBetween(1, 5);
                $contentPerSkpd[$skpd->id] = [];
                
                for ($k = 0; $k < $numContent; $k++) {
                    $content = Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publishers[$index]->id,
                        'kategori_id' => $kategori->id,
                        'status' => fake()->randomElement(Content::getStatusOptions()),
                    ]);
                    $contentPerSkpd[$skpd->id][] = $content->id;
                }
            }
            
            // Pick a random publisher to test
            $testPublisherIndex = fake()->numberBetween(0, $numSkpds - 1);
            $testPublisher = $publishers[$testPublisherIndex];
            $testSkpd = $skpds[$testPublisherIndex];
            
            // Get content via service
            $visibleContent = $this->contentService->getContentByPublisher($testPublisher);
            
            // Verify all returned content belongs to the publisher's SKPD
            foreach ($visibleContent as $content) {
                expect($content->skpd_id)->toBe($testSkpd->id,
                    "Publisher from SKPD {$testSkpd->id} should only see content from their own SKPD, but saw content from SKPD {$content->skpd_id}");
            }
            
            // Verify the count matches expected content for this SKPD
            $expectedContentIds = $contentPerSkpd[$testSkpd->id];
            expect($visibleContent->count())->toBe(count($expectedContentIds),
                "Publisher should see exactly " . count($expectedContentIds) . " content items from their SKPD, but saw " . $visibleContent->count());
            
            // Verify all expected content is present
            $visibleContentIds = $visibleContent->pluck('id')->toArray();
            foreach ($expectedContentIds as $expectedId) {
                expect(in_array($expectedId, $visibleContentIds))->toBeTrue(
                    "Content ID {$expectedId} from publisher's SKPD should be visible");
            }
            
            // Clean up for next iteration
            Content::whereIn('skpd_id', collect($skpds)->pluck('id'))->delete();
            User::whereIn('id', collect($publishers)->pluck('id'))->delete();
            foreach ($skpds as $skpd) {
                $skpd->delete();
            }
            $kategori->delete();
        }
    });

    /**
     * Property test: For any Publisher, content from other SKPDs should never
     * be included in their content list, even when filters are applied.
     */
    test('publisher cannot see content from other SKPDs even with filters', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create two SKPDs
            $skpd1 = Skpd::factory()->create(['status' => 'Active']);
            $skpd2 = Skpd::factory()->create(['status' => 'Active']);
            
            // Create publishers
            $publisher1 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd1->id,
            ]);
            $publisher2 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd2->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create content for both SKPDs with same status
            $status = fake()->randomElement(Content::getStatusOptions());
            
            $content1 = Content::factory()->create([
                'skpd_id' => $skpd1->id,
                'publisher_id' => $publisher1->id,
                'kategori_id' => $kategori->id,
                'status' => $status,
            ]);
            
            $content2 = Content::factory()->create([
                'skpd_id' => $skpd2->id,
                'publisher_id' => $publisher2->id,
                'kategori_id' => $kategori->id,
                'status' => $status,
            ]);
            
            // Apply filter with the same status
            $filters = ['status' => $status];
            
            // Get content for publisher1 with filter
            $visibleContent = $this->contentService->getContentByPublisher($publisher1, $filters);
            
            // Verify only content from publisher1's SKPD is returned
            foreach ($visibleContent as $content) {
                expect($content->skpd_id)->toBe($skpd1->id,
                    "Publisher1 should only see content from SKPD1, even with filters applied");
            }
            
            // Verify content2 (from SKPD2) is NOT in the results
            $visibleContentIds = $visibleContent->pluck('id')->toArray();
            expect(in_array($content2->id, $visibleContentIds))->toBeFalse(
                "Content from other SKPD should never be visible to publisher");
            
            // Clean up
            Content::whereIn('id', [$content1->id, $content2->id])->delete();
            User::whereIn('id', [$publisher1->id, $publisher2->id])->delete();
            $skpd1->delete();
            $skpd2->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any Publisher with no content in their SKPD,
     * the content list should be empty, even if other SKPDs have content.
     */
    test('publisher sees empty list when their SKPD has no content', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create two SKPDs
            $skpdWithContent = Skpd::factory()->create(['status' => 'Active']);
            $skpdWithoutContent = Skpd::factory()->create(['status' => 'Active']);
            
            // Create publishers
            $publisherWithContent = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpdWithContent->id,
            ]);
            $publisherWithoutContent = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpdWithoutContent->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create content only for the first SKPD
            $numContent = fake()->numberBetween(1, 5);
            for ($j = 0; $j < $numContent; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpdWithContent->id,
                    'publisher_id' => $publisherWithContent->id,
                    'kategori_id' => $kategori->id,
                ]);
            }
            
            // Get content for publisher without content
            $visibleContent = $this->contentService->getContentByPublisher($publisherWithoutContent);
            
            // Verify empty result
            expect($visibleContent->count())->toBe(0,
                "Publisher from SKPD without content should see empty list, but saw " . $visibleContent->count() . " items");
            
            // Clean up
            Content::where('skpd_id', $skpdWithContent->id)->delete();
            User::whereIn('id', [$publisherWithContent->id, $publisherWithoutContent->id])->delete();
            $skpdWithContent->delete();
            $skpdWithoutContent->delete();
            $kategori->delete();
        }
    });
});
