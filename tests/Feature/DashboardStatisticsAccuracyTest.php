<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 17: Dashboard statistics accuracy
 * 
 * *For any* given month and year, dashboard statistics (total SKPD, total content, 
 * pending content, non-compliant SKPD) should match the actual database counts.
 * 
 * **Validates: Requirements 5.1**
 */
describe('Property 17: Dashboard statistics accuracy', function () {
    
    beforeEach(function () {
        $this->reportService = new ReportService();
    });

    /**
     * Property test: Total SKPD count should match actual active SKPD count in database.
     */
    test('total SKPD count matches actual active SKPD count', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random counts
            $activeSkpdCount = fake()->numberBetween(0, 10);
            $inactiveSkpdCount = fake()->numberBetween(0, 5);
            
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create active SKPDs (only 'Active' is valid per database enum)
            for ($j = 0; $j < $activeSkpdCount; $j++) {
                Skpd::factory()->create([
                    'status' => 'Active',
                ]);
            }
            
            // Create inactive SKPDs (only 'Inactive' is valid per database enum)
            for ($j = 0; $j < $inactiveSkpdCount; $j++) {
                Skpd::factory()->create([
                    'status' => 'Inactive',
                ]);
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($month, $year);
            
            // Verify total SKPD matches active count
            expect($stats['total_skpd'])->toBe($activeSkpdCount,
                "Expected {$activeSkpdCount} active SKPDs, got {$stats['total_skpd']}");
            
            // Clean up for next iteration
            Skpd::query()->delete();
        }
    });

    /**
     * Property test: Total content this month should match actual content count for that month/year.
     */
    test('total content this month matches actual content count for specified month/year', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random month and year
            $targetMonth = fake()->numberBetween(1, 12);
            $targetYear = fake()->numberBetween(2020, 2025);
            
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content counts
            $targetMonthContentCount = fake()->numberBetween(0, 10);
            $otherMonthContentCount = fake()->numberBetween(0, 5);
            
            // Create content in target month/year
            for ($j = 0; $j < $targetMonthContentCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create content in different month (should NOT be counted)
            $differentMonth = $targetMonth === 12 ? 1 : $targetMonth + 1;
            for ($j = 0; $j < $otherMonthContentCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $differentMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($targetMonth, $targetYear);
            
            // Verify total content this month matches
            expect($stats['total_content_this_month'])->toBe($targetMonthContentCount,
                "Expected {$targetMonthContentCount} content for {$targetMonth}/{$targetYear}, got {$stats['total_content_this_month']}");
            
            // Clean up for next iteration
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Pending content count should match actual pending content count (regardless of date).
     */
    test('pending content count matches actual pending content count', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random month and year
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content counts by status
            $pendingCount = fake()->numberBetween(0, 10);
            $otherStatusCount = fake()->numberBetween(0, 5);
            
            // Create pending content
            for ($j = 0; $j < $pendingCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_PENDING,
                    'tanggal_publikasi' => fake()->date(),
                ]);
            }
            
            // Create content with other statuses
            $nonPendingStatuses = [Content::STATUS_DRAFT, Content::STATUS_APPROVED, Content::STATUS_REJECTED, Content::STATUS_PUBLISHED];
            for ($j = 0; $j < $otherStatusCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement($nonPendingStatuses),
                    'tanggal_publikasi' => fake()->date(),
                ]);
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($month, $year);
            
            // Verify pending content count matches
            expect($stats['pending_content'])->toBe($pendingCount,
                "Expected {$pendingCount} pending content, got {$stats['pending_content']}");
            
            // Clean up for next iteration
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Non-compliant SKPD count should match actual count of SKPDs not meeting quota.
     */
    test('non-compliant SKPD count matches actual count of SKPDs not meeting quota', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random month and year
            $targetMonth = fake()->numberBetween(1, 12);
            $targetYear = fake()->numberBetween(2020, 2025);
            
            // Generate random SKPD counts
            $compliantSkpdCount = fake()->numberBetween(0, 5);
            $nonCompliantSkpdCount = fake()->numberBetween(0, 5);
            
            $kategori = KategoriKonten::factory()->create();
            
            // Create compliant SKPDs (meeting quota)
            for ($j = 0; $j < $compliantSkpdCount; $j++) {
                $quota = fake()->numberBetween(1, 5);
                $skpd = Skpd::factory()->create([
                    'status' => 'Active',
                    'kuota_bulanan' => $quota,
                ]);
                
                $publisher = User::factory()->create([
                    'role' => 'Publisher',
                    'skpd_id' => $skpd->id,
                ]);
                
                // Create approved content >= quota
                for ($k = 0; $k < $quota; $k++) {
                    Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publisher->id,
                        'kategori_id' => $kategori->id,
                        'status' => Content::STATUS_APPROVED,
                        'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                    ]);
                }
            }
            
            // Create non-compliant SKPDs (not meeting quota)
            for ($j = 0; $j < $nonCompliantSkpdCount; $j++) {
                $quota = fake()->numberBetween(3, 10);
                $skpd = Skpd::factory()->create([
                    'status' => 'Active',
                    'kuota_bulanan' => $quota,
                ]);
                
                $publisher = User::factory()->create([
                    'role' => 'Publisher',
                    'skpd_id' => $skpd->id,
                ]);
                
                // Create approved content < quota (0 to quota-1)
                $approvedCount = fake()->numberBetween(0, $quota - 1);
                for ($k = 0; $k < $approvedCount; $k++) {
                    Content::factory()->create([
                        'skpd_id' => $skpd->id,
                        'publisher_id' => $publisher->id,
                        'kategori_id' => $kategori->id,
                        'status' => Content::STATUS_APPROVED,
                        'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                    ]);
                }
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($targetMonth, $targetYear);
            
            // Verify non-compliant SKPD count matches
            expect($stats['non_compliant_skpd'])->toBe($nonCompliantSkpdCount,
                "Expected {$nonCompliantSkpdCount} non-compliant SKPDs, got {$stats['non_compliant_skpd']}");
            
            // Clean up for next iteration
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Approved content this month should match actual approved content count for that month/year.
     */
    test('approved content this month matches actual approved content count for specified month/year', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random month and year
            $targetMonth = fake()->numberBetween(1, 12);
            $targetYear = fake()->numberBetween(2020, 2025);
            
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content counts
            $approvedCount = fake()->numberBetween(0, 10);
            $otherStatusCount = fake()->numberBetween(0, 5);
            
            // Create approved content in target month/year
            for ($j = 0; $j < $approvedCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => Content::STATUS_APPROVED,
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create content with other statuses in target month
            $nonApprovedStatuses = [Content::STATUS_DRAFT, Content::STATUS_PENDING, Content::STATUS_REJECTED, Content::STATUS_PUBLISHED];
            for ($j = 0; $j < $otherStatusCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement($nonApprovedStatuses),
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($targetMonth, $targetYear);
            
            // Verify approved content this month matches
            expect($stats['approved_content_this_month'])->toBe($approvedCount,
                "Expected {$approvedCount} approved content for {$targetMonth}/{$targetYear}, got {$stats['approved_content_this_month']}");
            
            // Clean up for next iteration
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Total content all time should match actual total content count in database.
     */
    test('total content all time matches actual total content count', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random month and year for the query
            $month = fake()->numberBetween(1, 12);
            $year = fake()->numberBetween(2020, 2025);
            
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random total content count
            $totalContentCount = fake()->numberBetween(0, 20);
            
            // Create content with various dates and statuses
            for ($j = 0; $j < $totalContentCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => fake()->date(),
                ]);
            }
            
            // Get dashboard stats
            $stats = $this->reportService->getDashboardStats($month, $year);
            
            // Verify total content all time matches
            expect($stats['total_content_all_time'])->toBe($totalContentCount,
                "Expected {$totalContentCount} total content, got {$stats['total_content_all_time']}");
            
            // Clean up for next iteration
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });
});
