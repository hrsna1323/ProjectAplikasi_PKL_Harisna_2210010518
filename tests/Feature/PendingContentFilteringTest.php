<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 11: Pending content filtering
 * 
 * *For any* Operator viewing the dashboard, only content with status "Pending"
 * should appear in the verification queue.
 * 
 * **Validates: Requirements 4.1**
 */
describe('Property 11: Pending content filtering', function () {
    
    beforeEach(function () {
        $this->verificationService = app(VerificationService::class);
    });

    /**
     * Property test: For any mix of content with various statuses,
     * getPendingContents should return ONLY content with status "Pending".
     */
    test('getPendingContents returns only content with Pending status', function () {
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
            
            // Generate random number of content for each status
            $allStatuses = Content::getStatusOptions();
            $createdContent = [];
            $expectedPendingCount = 0;
            
            foreach ($allStatuses as $status) {
                // Create random number of content (0-5) for each status
                $count = fake()->numberBetween(0, 5);
                
                for ($j = 0; $j < $count; $j++) {
                    $content = Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publisher->id,
                        'kategori_id' => $kategori->id,
                        'status' => $status,
                    ]);
                    $createdContent[] = $content;
                    
                    if ($status === Content::STATUS_PENDING) {
                        $expectedPendingCount++;
                    }
                }
            }
            
            // Get pending contents using the service
            $pendingContents = $this->verificationService->getPendingContents();
            
            // Verify count matches expected pending count
            expect($pendingContents->count())->toBe($expectedPendingCount,
                "Expected {$expectedPendingCount} pending contents, but got {$pendingContents->count()}");
            
            // Verify ALL returned content has status "Pending"
            foreach ($pendingContents as $content) {
                expect($content->status)->toBe(Content::STATUS_PENDING,
                    "All returned content should have status 'Pending', but found '{$content->status}'");
                expect($content->isPending())->toBeTrue();
            }
            
            // Verify NO non-pending content is returned
            $nonPendingStatuses = array_filter($allStatuses, fn($s) => $s !== Content::STATUS_PENDING);
            foreach ($nonPendingStatuses as $status) {
                $foundNonPending = $pendingContents->contains(fn($c) => $c->status === $status);
                expect($foundNonPending)->toBeFalse(
                    "Content with status '{$status}' should NOT appear in pending contents");
            }
            
            // Clean up for next iteration
            Content::whereIn('id', collect($createdContent)->pluck('id'))->delete();
            User::where('id', $publisher->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Pending content filtering with SKPD filter should return
     * only pending content from the specified SKPD.
     */
    test('getPendingContents with SKPD filter returns only pending content from that SKPD', function () {
        // Run 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Create multiple SKPDs
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
            
            // Create pending content for SKPD 1
            $pendingCountSkpd1 = fake()->numberBetween(1, 5);
            for ($j = 0; $j < $pendingCountSkpd1; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd1->id,
                    'publisher_id' => $publisher1->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_PENDING,
                ]);
            }
            
            // Create pending content for SKPD 2
            $pendingCountSkpd2 = fake()->numberBetween(1, 5);
            for ($j = 0; $j < $pendingCountSkpd2; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd2->id,
                    'publisher_id' => $publisher2->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_PENDING,
                ]);
            }
            
            // Create non-pending content for both SKPDs
            $nonPendingStatuses = [
                Content::STATUS_DRAFT,
                Content::STATUS_APPROVED,
                Content::STATUS_REJECTED,
            ];
            
            foreach ($nonPendingStatuses as $status) {
                Content::factory()->create([
                    'skpd_id' => $skpd1->id,
                    'publisher_id' => $publisher1->id,
                    'kategori_id' => $kategori->id,
                    'status' => $status,
                ]);
            }
            
            // Get pending contents filtered by SKPD 1
            $pendingContentsSkpd1 = $this->verificationService->getPendingContents([
                'skpd_id' => $skpd1->id,
            ]);
            
            // Verify count matches expected
            expect($pendingContentsSkpd1->count())->toBe($pendingCountSkpd1,
                "Expected {$pendingCountSkpd1} pending contents for SKPD 1");
            
            // Verify all returned content is pending AND from SKPD 1
            foreach ($pendingContentsSkpd1 as $content) {
                expect($content->status)->toBe(Content::STATUS_PENDING);
                expect($content->skpd_id)->toBe($skpd1->id,
                    "All returned content should be from SKPD 1");
            }
            
            // Clean up
            Content::whereIn('skpd_id', [$skpd1->id, $skpd2->id])->delete();
            User::whereIn('id', [$publisher1->id, $publisher2->id])->delete();
            $skpd1->delete();
            $skpd2->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: When no pending content exists, getPendingContents should return empty collection.
     */
    test('getPendingContents returns empty collection when no pending content exists', function () {
        // Run 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            
            // Create publisher
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create content with non-pending statuses only
            $nonPendingStatuses = [
                Content::STATUS_DRAFT,
                Content::STATUS_APPROVED,
                Content::STATUS_REJECTED,
                Content::STATUS_PUBLISHED,
            ];
            
            $createdContent = [];
            foreach ($nonPendingStatuses as $status) {
                $count = fake()->numberBetween(0, 3);
                for ($j = 0; $j < $count; $j++) {
                    $content = Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publisher->id,
                        'kategori_id' => $kategori->id,
                        'status' => $status,
                    ]);
                    $createdContent[] = $content;
                }
            }
            
            // Get pending contents
            $pendingContents = $this->verificationService->getPendingContents();
            
            // Verify empty collection is returned
            expect($pendingContents->count())->toBe(0,
                "Should return empty collection when no pending content exists");
            expect($pendingContents->isEmpty())->toBeTrue();
            
            // Clean up
            Content::whereIn('id', collect($createdContent)->pluck('id'))->delete();
            User::where('id', $publisher->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });
});
