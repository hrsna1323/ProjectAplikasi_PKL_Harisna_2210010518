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
 * Simplified tests for verification history functionality
 * Tests all properties with simpler, more maintainable approach
 */
class VerificationHistorySimpleTest extends TestCase
{
    use RefreshDatabase;

    private VerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verificationService = app(VerificationService::class);
    }

    /**
     * Property 2: Chronological ordering
     * Validates: Requirements 1.3
     */
    public function test_verification_history_is_ordered_chronologically(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create verifications with specific dates
        $dates = [
            now()->subDays(5),
            now()->subDays(2),
            now()->subDays(10),
            now(),
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

        // Act
        $result = $this->verificationService->getAllVerificationHistory([]);

        // Assert: Verify chronological ordering
        $previousTimestamp = PHP_INT_MAX;
        foreach ($result as $verification) {
            $currentTimestamp = $verification->verified_at->timestamp;
            $this->assertLessThanOrEqual($previousTimestamp, $currentTimestamp);
            $previousTimestamp = $currentTimestamp;
        }
    }

    /**
     * Property 3: Filter correctness - Date range
     * Validates: Requirements 1.4
     */
    public function test_date_range_filter_returns_correct_verifications(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create verifications with various dates
        $content1 = Content::factory()->create(['skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content1->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()->subDays(10)]);

        $content2 = Content::factory()->create(['skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content2->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()->subDays(5)]);

        $content3 = Content::factory()->create(['skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content3->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);

        // Act: Filter last 7 days
        $result = $this->verificationService->getAllVerificationHistory([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

        // Assert: Should only return verifications within date range
        $this->assertEquals(2, $result->count());
    }

    /**
     * Property 3: Filter correctness - SKPD filter
     * Validates: Requirements 1.4
     */
    public function test_skpd_filter_returns_correct_verifications(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd1 = Skpd::factory()->create(['nama_skpd' => 'SKPD 1']);
        $skpd2 = Skpd::factory()->create(['nama_skpd' => 'SKPD 2']);
        $kategori = KategoriKonten::factory()->create();

        // Create verifications for different SKPDs
        $content1 = Content::factory()->create(['skpd_id' => $skpd1->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content1->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);

        $content2 = Content::factory()->create(['skpd_id' => $skpd2->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content2->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);

        // Act: Filter by SKPD 1
        $result = $this->verificationService->getAllVerificationHistory([
            'skpd_id' => $skpd1->id,
        ]);

        // Assert
        $this->assertEquals(1, $result->count());
        $this->assertEquals($skpd1->id, $result->first()->content->skpd_id);
    }

    /**
     * Property 3: Filter correctness - Status filter
     * Validates: Requirements 1.4
     */
    public function test_status_filter_returns_correct_verifications(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create approved and rejected verifications
        $content1 = Content::factory()->create(['skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id, 'status' => 'Approved']);
        Verification::create(['content_id' => $content1->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);

        $content2 = Content::factory()->create(['skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id, 'status' => 'Rejected']);
        Verification::create(['content_id' => $content2->id, 'verifikator_id' => $operator->id, 'status' => 'Rejected', 'alasan' => 'Test', 'verified_at' => now()]);

        // Act: Filter by Approved
        $result = $this->verificationService->getAllVerificationHistory([
            'status' => 'Approved',
        ]);

        // Assert
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Approved', $result->first()->status);
    }

    /**
     * Property 1: Verification record completeness
     * Property 4: Content detail link presence
     * Validates: Requirements 1.2, 1.5
     */
    public function test_verification_history_page_displays_complete_information(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator', 'name' => 'Test Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create(['nama_skpd' => 'Test SKPD']);
        $kategori = KategoriKonten::factory()->create(['nama_kategori' => 'Test Category']);

        $content = Content::factory()->create([
            'judul' => 'Test Content Title',
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

        // Act
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert: All required fields present
        $response->assertStatus(200);
        $response->assertSee('Test Content Title'); // Content title
        $response->assertSee('Test SKPD'); // SKPD name
        $response->assertSee('Approved'); // Status
        $response->assertSee('Test Operator'); // Operator name
        $response->assertSee('Test Category'); // Category
        $response->assertSee('Lihat Detail'); // Detail link text
    }

    /**
     * Property 5: Content-specific history isolation
     * Validates: Requirements 2.3
     */
    public function test_content_specific_history_returns_only_related_verifications(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create two contents with verifications
        $content1 = Content::factory()->create(['judul' => 'Content 1', 'skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content1->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);
        Verification::create(['content_id' => $content1->id, 'verifikator_id' => $operator->id, 'status' => 'Rejected', 'alasan' => 'Test', 'verified_at' => now()->subHour()]);

        $content2 = Content::factory()->create(['judul' => 'Content 2', 'skpd_id' => $skpd->id, 'kategori_id' => $kategori->id, 'publisher_id' => $publisher->id]);
        Verification::create(['content_id' => $content2->id, 'verifikator_id' => $operator->id, 'status' => 'Approved', 'verified_at' => now()]);

        // Act: Get history for content 1
        $history = $this->verificationService->getVerificationHistory($content1);

        // Assert: Should only return verifications for content 1
        $this->assertCount(2, $history);
        foreach ($history as $verification) {
            $this->assertEquals($content1->id, $verification->content_id);
        }
    }

    /**
     * Test that the new route works and doesn't require parameters
     */
    public function test_general_history_route_works_without_parameters(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);

        // Act
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Riwayat Verifikasi');
    }

    /**
     * Test that sidebar link uses correct route
     */
    public function test_sidebar_link_uses_correct_route(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);

        // Act
        $this->actingAs($operator);
        $response = $this->get(route('operator.dashboard'));

        // Assert: Should not throw error and should contain the correct route
        $response->assertStatus(200);
        $response->assertSee(route('operator.verification.history.index'), false);
    }

    /**
     * Test that verification index page uses correct route
     */
    public function test_verification_index_page_uses_correct_route(): void
    {
        // Arrange
        $operator = User::factory()->create(['role' => 'Operator']);

        // Act
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.index'));

        // Assert: Should not throw error and should contain the correct route
        $response->assertStatus(200);
        $response->assertSee(route('operator.verification.history.index'), false);
    }
}
