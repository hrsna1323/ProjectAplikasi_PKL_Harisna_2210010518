<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 18: Content history filtering
 * 
 * *For any* filter combination (SKPD, period, status, kategori), the content history 
 * should return only records matching all specified criteria.
 * 
 * **Validates: Requirements 6.1, 8.4, 9.3**
 */
describe('Property 18: Content history filtering', function () {
    
    beforeEach(function () {
        $this->reportService = new ReportService();
    });

    /**
     * Property test: Filtering by SKPD should return only content from that SKPD.
     */
    test('filtering by SKPD returns only content from that SKPD', function () {
        for ($i = 0; $i < 100; $i++) {
            // Create multiple SKPDs
            $targetSkpd = Skpd::factory()->create(['status' => 'Active']);
            $otherSkpd = Skpd::factory()->create(['status' => 'Active']);
            
            $kategori = KategoriKonten::factory()->create();
            
            $publisher1 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $targetSkpd->id,
            ]);
            $publisher2 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $otherSkpd->id,
            ]);
            
            // Generate random content counts
            $targetSkpdContentCount = fake()->numberBetween(1, 5);
            $otherSkpdContentCount = fake()->numberBetween(1, 5);
            
            // Create content for target SKPD
            for ($j = 0; $j < $targetSkpdContentCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $targetSkpd->id,
                    'publisher_id' => $publisher1->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Create content for other SKPD
            for ($j = 0; $j < $otherSkpdContentCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $otherSkpd->id,
                    'publisher_id' => $publisher2->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Filter by target SKPD
            $result = $this->reportService->getContentHistory(['skpd_id' => $targetSkpd->id]);
            
            // Verify all results are from target SKPD
            expect($result->count())->toBe($targetSkpdContentCount);
            foreach ($result as $content) {
                expect($content->skpd_id)->toBe($targetSkpd->id);
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Filtering by status should return only content with that status.
     */
    test('filtering by status returns only content with that status', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $kategori = KategoriKonten::factory()->create();
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Pick a random target status
            $targetStatus = fake()->randomElement(Content::getStatusOptions());
            $otherStatuses = array_filter(Content::getStatusOptions(), fn($s) => $s !== $targetStatus);
            
            // Generate random content counts
            $targetStatusCount = fake()->numberBetween(1, 5);
            $otherStatusCount = fake()->numberBetween(1, 5);
            
            // Create content with target status
            for ($j = 0; $j < $targetStatusCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => $targetStatus,
                ]);
            }
            
            // Create content with other statuses
            for ($j = 0; $j < $otherStatusCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement($otherStatuses),
                ]);
            }
            
            // Filter by target status
            $result = $this->reportService->getContentHistory(['status' => $targetStatus]);
            
            // Verify all results have target status
            expect($result->count())->toBe($targetStatusCount);
            foreach ($result as $content) {
                expect($content->status)->toBe($targetStatus);
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Filtering by kategori should return only content with that kategori.
     */
    test('filtering by kategori returns only content with that kategori', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $targetKategori = KategoriKonten::factory()->create();
            $otherKategori = KategoriKonten::factory()->create();
            
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Generate random content counts
            $targetKategoriCount = fake()->numberBetween(1, 5);
            $otherKategoriCount = fake()->numberBetween(1, 5);
            
            // Create content with target kategori
            for ($j = 0; $j < $targetKategoriCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $targetKategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Create content with other kategori
            for ($j = 0; $j < $otherKategoriCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $otherKategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Filter by target kategori
            $result = $this->reportService->getContentHistory(['kategori_id' => $targetKategori->id]);
            
            // Verify all results have target kategori
            expect($result->count())->toBe($targetKategoriCount);
            foreach ($result as $content) {
                expect($content->kategori_id)->toBe($targetKategori->id);
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Filtering by date range should return only content within that range.
     */
    test('filtering by date range returns only content within that range', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $kategori = KategoriKonten::factory()->create();
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Define date range (random month in 2024)
            $startDate = '2024-06-01';
            $endDate = '2024-06-30';
            
            // Generate random content counts
            $inRangeCount = fake()->numberBetween(1, 5);
            $outOfRangeCount = fake()->numberBetween(1, 5);
            
            // Create content within date range
            for ($j = 0; $j < $inRangeCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => sprintf('2024-06-%02d', fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create content outside date range
            for ($j = 0; $j < $outOfRangeCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => '2024-05-15', // Before range
                ]);
            }
            
            // Filter by date range
            $result = $this->reportService->getContentHistory([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            
            // Verify all results are within date range
            expect($result->count())->toBe($inRangeCount);
            foreach ($result as $content) {
                $pubDate = $content->tanggal_publikasi->format('Y-m-d');
                expect($pubDate >= $startDate && $pubDate <= $endDate)->toBeTrue();
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Filtering by month/year should return only content from that month/year.
     */
    test('filtering by month and year returns only content from that period', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $kategori = KategoriKonten::factory()->create();
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Pick random target month/year
            $targetMonth = fake()->numberBetween(1, 12);
            $targetYear = fake()->numberBetween(2020, 2025);
            
            // Generate random content counts
            $targetPeriodCount = fake()->numberBetween(1, 5);
            $otherPeriodCount = fake()->numberBetween(1, 5);
            
            // Create content in target month/year
            for ($j = 0; $j < $targetPeriodCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $targetMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Create content in different month
            $differentMonth = $targetMonth === 12 ? 1 : $targetMonth + 1;
            for ($j = 0; $j < $otherPeriodCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                    'tanggal_publikasi' => sprintf('%04d-%02d-%02d', $targetYear, $differentMonth, fake()->numberBetween(1, 28)),
                ]);
            }
            
            // Filter by month/year
            $result = $this->reportService->getContentHistory([
                'month' => $targetMonth,
                'year' => $targetYear,
            ]);
            
            // Verify all results are from target month/year
            expect($result->count())->toBe($targetPeriodCount);
            foreach ($result as $content) {
                expect((int) $content->tanggal_publikasi->format('m'))->toBe($targetMonth);
                expect((int) $content->tanggal_publikasi->format('Y'))->toBe($targetYear);
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Combined filters should return only content matching ALL criteria.
     */
    test('combined filters return only content matching all criteria', function () {
        for ($i = 0; $i < 100; $i++) {
            // Create test data
            $targetSkpd = Skpd::factory()->create(['status' => 'Active']);
            $otherSkpd = Skpd::factory()->create(['status' => 'Active']);
            $targetKategori = KategoriKonten::factory()->create();
            $otherKategori = KategoriKonten::factory()->create();
            
            $publisher1 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $targetSkpd->id,
            ]);
            $publisher2 = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $otherSkpd->id,
            ]);
            
            $targetStatus = Content::STATUS_APPROVED;
            
            // Generate random count for matching content
            $matchingCount = fake()->numberBetween(1, 3);
            
            // Create content matching ALL criteria
            for ($j = 0; $j < $matchingCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $targetSkpd->id,
                    'publisher_id' => $publisher1->id,
                    'kategori_id' => $targetKategori->id,
                    'status' => $targetStatus,
                ]);
            }
            
            // Create content matching only some criteria (should NOT be returned)
            // Wrong SKPD
            Content::factory()->create([
                'skpd_id' => $otherSkpd->id,
                'publisher_id' => $publisher2->id,
                'kategori_id' => $targetKategori->id,
                'status' => $targetStatus,
            ]);
            
            // Wrong kategori
            Content::factory()->create([
                'skpd_id' => $targetSkpd->id,
                'publisher_id' => $publisher1->id,
                'kategori_id' => $otherKategori->id,
                'status' => $targetStatus,
            ]);
            
            // Wrong status
            Content::factory()->create([
                'skpd_id' => $targetSkpd->id,
                'publisher_id' => $publisher1->id,
                'kategori_id' => $targetKategori->id,
                'status' => Content::STATUS_PENDING,
            ]);
            
            // Apply combined filters
            $result = $this->reportService->getContentHistory([
                'skpd_id' => $targetSkpd->id,
                'kategori_id' => $targetKategori->id,
                'status' => $targetStatus,
            ]);
            
            // Verify only matching content is returned
            expect($result->count())->toBe($matchingCount);
            foreach ($result as $content) {
                expect($content->skpd_id)->toBe($targetSkpd->id);
                expect($content->kategori_id)->toBe($targetKategori->id);
                expect($content->status)->toBe($targetStatus);
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Empty filters should return all content.
     */
    test('empty filters return all content', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $kategori = KategoriKonten::factory()->create();
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Generate random total content count
            $totalCount = fake()->numberBetween(1, 10);
            
            // Create content with various attributes
            for ($j = 0; $j < $totalCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Get content with empty filters
            $result = $this->reportService->getContentHistory([]);
            
            // Verify all content is returned
            expect($result->count())->toBe($totalCount);
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });

    /**
     * Property test: Search filter should return only content with matching title.
     */
    test('search filter returns only content with matching title', function () {
        for ($i = 0; $i < 100; $i++) {
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $kategori = KategoriKonten::factory()->create();
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Generate unique search term
            $searchTerm = 'UniqueSearchTerm' . fake()->uuid();
            
            // Generate random content counts
            $matchingCount = fake()->numberBetween(1, 3);
            $nonMatchingCount = fake()->numberBetween(1, 3);
            
            // Create content with matching title
            for ($j = 0; $j < $matchingCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'judul' => "Artikel tentang {$searchTerm} yang menarik",
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Create content without matching title
            for ($j = 0; $j < $nonMatchingCount; $j++) {
                Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $kategori->id,
                    'judul' => 'Artikel biasa tanpa kata kunci',
                    'status' => fake()->randomElement(Content::getStatusOptions()),
                ]);
            }
            
            // Filter by search term
            $result = $this->reportService->getContentHistory(['search' => $searchTerm]);
            
            // Verify only matching content is returned
            expect($result->count())->toBe($matchingCount);
            foreach ($result as $content) {
                expect(str_contains($content->judul, $searchTerm))->toBeTrue();
            }
            
            // Clean up
            Content::query()->delete();
            User::where('role', 'Publisher')->delete();
            Skpd::query()->delete();
            KategoriKonten::query()->delete();
        }
    });
});
