<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Verification;
use App\Models\KategoriKonten;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: fix-verification-history-route, Property 3: Filter correctness
 * 
 * Property: For any valid filter combination (date range, SKPD, status), 
 * all returned verification records should match the specified filter criteria
 * 
 * Validates: Requirements 1.4
 */
class VerificationHistoryFilterCorrectnessTest extends TestCase
{
    use RefreshDatabase;

    private VerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verificationService = app(VerificationService::class);
    }

    /**
     * Test that date range filter returns only verifications within the specified range
     * 
     * @dataProvider dateRangeFilterProvider
     */
    public function test_date_range_filter_returns_correct_verifications(string $startDate, string $endDate, int $expectedCount): void
    {
        // Arrange: Create verifications with various dates
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        $dates = [
            now()->subDays(10)->format('Y-m-d H:i:s'),
            now()->subDays(5)->format('Y-m-d H:i:s'),
            now()->subDays(3)->format('Y-m-d H:i:s'),
            now()->format('Y-m-d H:i:s'),
        ];

        foreach ($dates as $date) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'verified_at' => $date,
            ]);
        }

        // Act: Apply date range filter
        $result = $this->verificationService->getAllVerificationHistory([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Assert: All results should be within date range
        foreach ($result as $verification) {
            $verifiedDate = $verification->verified_at->format('Y-m-d');
            
            $this->assertGreaterThanOrEqual(
                $startDate,
                $verifiedDate,
                "Verification date {$verifiedDate} should be >= start date {$startDate}"
            );
            
            $this->assertLessThanOrEqual(
                $endDate,
                $verifiedDate,
                "Verification date {$verifiedDate} should be <= end date {$endDate}"
            );
        }
    }

    /**
     * Test that SKPD filter returns only verifications for the specified SKPD
     * 
     * @dataProvider skpdFilterProvider
     */
    public function test_skpd_filter_returns_correct_verifications(int $numSkpds, int $targetSkpdIndex): void
    {
        // Arrange: Create multiple SKPDs with verifications
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $kategori = KategoriKonten::factory()->create();
        
        $skpds = [];
        for ($i = 0; $i < $numSkpds; $i++) {
            $skpds[] = Skpd::factory()->create();
        }

        // Create verifications for each SKPD
        foreach ($skpds as $skpd) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'verified_at' => now(),
            ]);
        }

        $targetSkpd = $skpds[$targetSkpdIndex];

        // Act: Apply SKPD filter
        $result = $this->verificationService->getAllVerificationHistory([
            'skpd_id' => $targetSkpd->id,
        ]);

        // Assert: All results should belong to the target SKPD
        foreach ($result as $verification) {
            $this->assertEquals(
                $targetSkpd->id,
                $verification->content->skpd_id,
                'All verifications should belong to the filtered SKPD'
            );
        }

        $this->assertGreaterThan(0, $result->count(), 'Should return at least one verification for the target SKPD');
    }

    /**
     * Test that status filter returns only verifications with the specified status
     * 
     * @dataProvider statusFilterProvider
     */
    public function test_status_filter_returns_correct_verifications(string $filterStatus, int $approvedCount, int $rejectedCount): void
    {
        // Arrange: Create verifications with different statuses
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create approved verifications
        for ($i = 0; $i < $approvedCount; $i++) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
                'status' => 'Approved',
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'verified_at' => now(),
            ]);
        }

        // Create rejected verifications
        for ($i = 0; $i < $rejectedCount; $i++) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
                'status' => 'Rejected',
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Rejected',
                'alasan' => 'Test rejection reason',
                'verified_at' => now(),
            ]);
        }

        // Act: Apply status filter
        $result = $this->verificationService->getAllVerificationHistory([
            'status' => $filterStatus,
        ]);

        // Assert: All results should have the filtered status
        foreach ($result as $verification) {
            $this->assertEquals(
                $filterStatus,
                $verification->status,
                "All verifications should have status: {$filterStatus}"
            );
        }

        $expectedCount = $filterStatus === 'Approved' ? $approvedCount : $rejectedCount;
        $this->assertEquals($expectedCount, $result->count(), "Should return exactly {$expectedCount} verifications");
    }

    /**
     * Test that search filter returns only verifications matching the search term
     * 
     * @dataProvider searchFilterProvider
     */
    public function test_search_filter_returns_matching_verifications(string $searchTerm, array $contentTitles, int $expectedMatches): void
    {
        // Arrange: Create verifications with various content titles
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        foreach ($contentTitles as $title) {
            $content = Content::factory()->create([
                'judul' => $title,
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'verified_at' => now(),
            ]);
        }

        // Act: Apply search filter
        $result = $this->verificationService->getAllVerificationHistory([
            'search' => $searchTerm,
        ]);

        // Assert: All results should contain the search term
        foreach ($result as $verification) {
            $this->assertStringContainsStringIgnoringCase(
                $searchTerm,
                $verification->content->judul,
                "Content title should contain search term: {$searchTerm}"
            );
        }

        $this->assertEquals($expectedMatches, $result->count(), "Should return exactly {$expectedMatches} matching verifications");
    }

    /**
     * Data providers
     */
    public static function dateRangeFilterProvider(): array
    {
        return [
            'last_7_days' => [now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d'), 4],
            'last_5_days' => [now()->subDays(5)->format('Y-m-d'), now()->format('Y-m-d'), 3],
            'last_3_days' => [now()->subDays(3)->format('Y-m-d'), now()->format('Y-m-d'), 2],
        ];
    }

    public static function skpdFilterProvider(): array
    {
        $testCases = [];
        
        // Generate 50 test cases with random SKPD counts
        for ($i = 0; $i < 50; $i++) {
            $numSkpds = rand(2, 5);
            $targetIndex = rand(0, $numSkpds - 1);
            $testCases["random_skpd_$i"] = [$numSkpds, $targetIndex];
        }
        
        return $testCases;
    }

    public static function statusFilterProvider(): array
    {
        $testCases = [];
        
        // Generate 50 test cases with random status combinations
        for ($i = 0; $i < 50; $i++) {
            $approvedCount = rand(1, 10);
            $rejectedCount = rand(1, 10);
            $filterStatus = rand(0, 1) === 0 ? 'Approved' : 'Rejected';
            
            $testCases["random_status_$i"] = [$filterStatus, $approvedCount, $rejectedCount];
        }
        
        return $testCases;
    }

    public static function searchFilterProvider(): array
    {
        return [
            'search_berita' => [
                'Berita',
                ['Berita Terbaru', 'Pengumuman Penting', 'Berita Daerah', 'Informasi Publik'],
                2
            ],
            'search_pengumuman' => [
                'Pengumuman',
                ['Berita Terbaru', 'Pengumuman Penting', 'Berita Daerah', 'Pengumuman Resmi'],
                2
            ],
            'search_informasi' => [
                'Informasi',
                ['Berita Terbaru', 'Pengumuman Penting', 'Informasi Publik', 'Informasi Daerah'],
                2
            ],
        ];
    }
}
