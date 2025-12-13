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
 * Feature: manajemen-konten-skpd, Property 6: Quota compliance calculation
 * 
 * *For any* SKPD and given month/year, the compliance status should be correctly 
 * calculated based on approved content count versus quota.
 * 
 * **Validates: Requirements 2.5, 5.2, 5.4**
 */
describe('Property 6: Quota compliance calculation', function () {
    
    beforeEach(function () {
        $this->activityLogService = Mockery::mock(ActivityLogService::class);
        $this->activityLogService->shouldReceive('logUserAction')->andReturn(null);
        $this->activityLogService->shouldReceive('logSkpdUpdated')->andReturn(null);
        
        $this->skpdService = new SkpdService($this->activityLogService);
    });

    /**
     * Property test: For any SKPD with quota > 0 and approved content count >= quota,
     * compliance status should be "Memenuhi".
     */
    test('SKPD meeting quota returns Memenuhi status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random quota between 1 and 10
            $quota = fake()->numberBetween(1, 10);
            
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD with specific quota
            $skpd = Skpd::factory()->create([
                'kuota_bulanan' => $quota,
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create approved content count >= quota (meeting or exceeding)
            $approvedCount = fake()->numberBetween($quota, $quota + 5);
            
            for ($j = 0; $j < $approvedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $year, $month, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Calculate compliance status
            $status = $this->skpdService->calculateComplianceStatus($skpd, $month, $year);
            
            // Verify status is "Memenuhi"
            expect($status)->toBe('Memenuhi', 
                "SKPD with {$approvedCount} approved content and quota {$quota} should be 'Memenuhi'");
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any SKPD with quota > 0 and approved content count >= 50% but < 100% of quota,
     * compliance status should be "Sebagian".
     */
    test('SKPD partially meeting quota returns Sebagian status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate quota that allows for partial compliance (at least 2 to have meaningful 50%)
            $quota = fake()->numberBetween(2, 10);
            
            // Calculate approved count that is >= 50% but < 100% of quota
            $minApproved = (int) ceil($quota * 0.5);
            $maxApproved = $quota - 1;
            
            // Skip if no valid range exists
            if ($minApproved > $maxApproved) {
                continue;
            }
            
            $approvedCount = fake()->numberBetween($minApproved, $maxApproved);
            
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD with specific quota
            $skpd = Skpd::factory()->create([
                'kuota_bulanan' => $quota,
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create approved content
            for ($j = 0; $j < $approvedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $year, $month, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Calculate compliance status
            $status = $this->skpdService->calculateComplianceStatus($skpd, $month, $year);
            
            // Verify status is "Sebagian"
            $percentage = ($approvedCount / $quota) * 100;
            expect($status)->toBe('Sebagian', 
                "SKPD with {$approvedCount} approved content and quota {$quota} ({$percentage}%) should be 'Sebagian'");
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any SKPD with quota > 0 and approved content count < 50% of quota,
     * compliance status should be "Belum Memenuhi".
     */
    test('SKPD not meeting quota returns Belum Memenuhi status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate quota that allows for < 50% compliance (at least 2)
            $quota = fake()->numberBetween(2, 10);
            
            // Calculate approved count that is < 50% of quota
            $maxApproved = (int) floor($quota * 0.5) - 1;
            
            // Ensure we have a valid range (0 to maxApproved)
            if ($maxApproved < 0) {
                $maxApproved = 0;
            }
            
            $approvedCount = fake()->numberBetween(0, $maxApproved);
            
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD with specific quota
            $skpd = Skpd::factory()->create([
                'kuota_bulanan' => $quota,
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create approved content (if any)
            for ($j = 0; $j < $approvedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $year, $month, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Calculate compliance status
            $status = $this->skpdService->calculateComplianceStatus($skpd, $month, $year);
            
            // Verify status is "Belum Memenuhi"
            $percentage = ($approvedCount / $quota) * 100;
            expect($status)->toBe('Belum Memenuhi', 
                "SKPD with {$approvedCount} approved content and quota {$quota} ({$percentage}%) should be 'Belum Memenuhi'");
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any SKPD with quota <= 0,
     * compliance status should be "Tidak Ada Kuota".
     */
    test('SKPD with zero or negative quota returns Tidak Ada Kuota status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate zero or negative quota
            $quota = fake()->numberBetween(-5, 0);
            
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD with zero/negative quota
            $skpd = Skpd::factory()->create([
                'kuota_bulanan' => $quota,
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Randomly add some approved content (shouldn't affect result)
            $approvedCount = fake()->numberBetween(0, 5);
            for ($j = 0; $j < $approvedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $year, $month, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Calculate compliance status
            $status = $this->skpdService->calculateComplianceStatus($skpd, $month, $year);
            
            // Verify status is "Tidak Ada Kuota"
            expect($status)->toBe('Tidak Ada Kuota', 
                "SKPD with quota {$quota} should return 'Tidak Ada Kuota' regardless of content count");
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Only approved content in the specified month/year should count toward quota.
     * Content with other statuses or different dates should not affect compliance calculation.
     */
    test('only approved content in specified month/year counts toward quota', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $quota = fake()->numberBetween(3, 10);
            $targetMonth = fake()->numberBetween(1, 12);
            $targetYear = fake()->numberBetween(2020, 2025);
            
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'kuota_bulanan' => $quota,
                'status' => 'Active',
            ]);
            
            // Create publisher
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create approved content in target month/year (this should count)
            $targetApprovedCount = fake()->numberBetween(0, $quota);
            for ($j = 0; $j < $targetApprovedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create content with non-approved statuses in target month (should NOT count)
            $nonApprovedStatuses = [Content::STATUS_DRAFT, Content::STATUS_PENDING, Content::STATUS_REJECTED, Content::STATUS_PUBLISHED];
            foreach ($nonApprovedStatuses as $nonApprovedStatus) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => $nonApprovedStatus,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create approved content in different month (should NOT count)
            $differentMonth = $targetMonth === 12 ? 1 : $targetMonth + 1;
            Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => Content::STATUS_APPROVED,
                'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $differentMonth, fake()->numberBetween(1, 28)),
            ]);
            
            // Create approved content in different year (should NOT count)
            $differentYear = $targetYear + 1;
            Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => Content::STATUS_APPROVED,
                'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $differentYear, $targetMonth, fake()->numberBetween(1, 28)),
            ]);
            
            // Calculate compliance status
            $status = $this->skpdService->calculateComplianceStatus($skpd, $targetMonth, $targetYear);
            
            // Calculate expected status based only on target approved count
            $percentage = ($targetApprovedCount / $quota) * 100;
            $expectedStatus = match (true) {
                $percentage >= 100 => 'Memenuhi',
                $percentage >= 50 => 'Sebagian',
                default => 'Belum Memenuhi',
            };
            
            expect($status)->toBe($expectedStatus, 
                "Only {$targetApprovedCount} approved content in {$targetMonth}/{$targetYear} should count. Expected '{$expectedStatus}', got '{$status}'");
            
            // Clean up for next iteration
            Content::where('skpd_id', $skpd->id)->delete();
            User::where('skpd_id', $skpd->id)->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });
});
