<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\SkpdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 5: SKPD deletion constraint
 * 
 * *For any* SKPD with active content (status Pending, Approved, or Published), 
 * deletion attempts should be rejected.
 * 
 * **Validates: Requirements 2.4**
 */
describe('Property 5: SKPD deletion constraint', function () {
    
    beforeEach(function () {
        $this->activityLogService = Mockery::mock(ActivityLogService::class);
        $this->activityLogService->shouldReceive('logUserAction')->andReturn(null);
        $this->activityLogService->shouldReceive('logSkpdUpdated')->andReturn(null);
        
        $this->skpdService = new SkpdService($this->activityLogService);
    });

    /**
     * Property test: For any SKPD with active content (Pending, Approved, Published),
     * deletion should be rejected.
     */
    test('SKPD with active content cannot be deleted', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random SKPD
            $skpd = Skpd::factory()->create();
            
            // Generate random publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Generate random category
            $kategori = KategoriKonten::factory()->create();
            
            // Randomly select an active status (Pending, Approved, or Published)
            $activeStatuses = [Content::STATUS_PENDING, Content::STATUS_APPROVED, Content::STATUS_PUBLISHED];
            $randomActiveStatus = fake()->randomElement($activeStatuses);
            
            // Create content with active status
            Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => $randomActiveStatus,
            ]);
            
            // Attempt to delete SKPD should throw exception
            $exceptionThrown = false;
            try {
                $this->skpdService->deleteSkpd($skpd);
            } catch (\Exception $e) {
                $exceptionThrown = true;
                expect($e->getMessage())->toContain('tidak dapat dihapus');
            }
            
            expect($exceptionThrown)->toBeTrue("SKPD with {$randomActiveStatus} content should not be deletable");
            
            // Verify SKPD still exists in database
            expect(Skpd::find($skpd->id))->not->toBeNull();
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any SKPD without active content (only Draft or Rejected, or no content),
     * deletion should succeed.
     */
    test('SKPD without active content can be deleted', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random SKPD
            $skpd = Skpd::factory()->create();
            
            // Generate random publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Generate random category
            $kategori = KategoriKonten::factory()->create();
            
            // Randomly decide: no content, only Draft, only Rejected, or mix of Draft/Rejected
            $scenario = fake()->randomElement(['no_content', 'draft_only', 'rejected_only', 'draft_and_rejected']);
            
            if ($scenario !== 'no_content') {
                $inactiveStatuses = [];
                if ($scenario === 'draft_only') {
                    $inactiveStatuses = [Content::STATUS_DRAFT];
                } elseif ($scenario === 'rejected_only') {
                    $inactiveStatuses = [Content::STATUS_REJECTED];
                } else {
                    $inactiveStatuses = [Content::STATUS_DRAFT, Content::STATUS_REJECTED];
                }
                
                // Create 1-3 contents with inactive statuses
                $contentCount = fake()->numberBetween(1, 3);
                for ($j = 0; $j < $contentCount; $j++) {
                    Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publisher->id,
                        'kategori_id' => $kategori->id,
                        'status' => fake()->randomElement($inactiveStatuses),
                    ]);
                }
            }
            
            // First, delete associated content and users to avoid FK constraint
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            
            // Attempt to delete SKPD should succeed
            $result = $this->skpdService->deleteSkpd($skpd);
            
            expect($result)->toBeTrue();
            
            // Verify SKPD no longer exists in database
            expect(Skpd::find($skpd->id))->toBeNull();
            
            // Clean up kategori
            $kategori->delete();
        }
    });

    /**
     * Property test: Verify each active status individually blocks deletion.
     */
    test('each active status type blocks SKPD deletion', function () {
        $activeStatuses = [Content::STATUS_PENDING, Content::STATUS_APPROVED, Content::STATUS_PUBLISHED];
        
        foreach ($activeStatuses as $status) {
            // Run multiple iterations for each status
            for ($i = 0; $i < 30; $i++) {
                $skpd = Skpd::factory()->create();
                $publisher = User::factory()->create([
                    'role' => 'Publisher',
                    'skpd_id' => $skpd->id,
                ]);
                $kategori = KategoriKonten::factory()->create();
                
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => $status,
                ]);
                
                expect(fn() => $this->skpdService->deleteSkpd($skpd))
                    ->toThrow(\Exception::class);
                
                // Verify SKPD still exists
                expect(Skpd::find($skpd->id))->not->toBeNull();
                
                // Clean up
                Content::where('skpd_id', $skpd->id)->delete();
                User::where('skpd_id', $skpd->id)->delete();
                $skpd->delete();
                $kategori->delete();
            }
        }
    });
});
