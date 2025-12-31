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
 * Feature: fix-verification-history-route, Property 5: Content-specific history isolation
 * 
 * Property: For any valid content ID, the content-specific history route should return 
 * only verification records associated with that content
 * 
 * Validates: Requirements 2.3
 */
class ContentSpecificHistoryIsolationTest extends TestCase
{
    use RefreshDatabase;

    private VerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verificationService = app(VerificationService::class);
    }

    /**
     * Test that content-specific history returns only verifications for that content
     * 
     * @dataProvider contentHistoryProvider
     */
    public function test_content_specific_history_returns_only_related_verifications(
        int $numContents,
        int $verificationsPerContent
    ): void {
        // Arrange: Create multiple contents with verifications
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        $contents = [];
        $verificationsByContent = [];

        for ($i = 0; $i < $numContents; $i++) {
            $content = Content::factory()->create([
                'judul' => "Content $i",
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
            ]);

            $contents[] = $content;
            $verificationsByContent[$content->id] = [];

            // Create multiple verifications for this content
            for ($j = 0; $j < $verificationsPerContent; $j++) {
                $verification = Verification::create([
                    'content_id' => $content->id,
                    'verifikator_id' => $operator->id,
                    'status' => $j % 2 === 0 ? 'Approved' : 'Rejected',
                    'verified_at' => now()->subHours($j),
                ]);

                $verificationsByContent[$content->id][] = $verification->id;
            }
        }

        // Act & Assert: For each content, verify history isolation
        foreach ($contents as $content) {
            $history = $this->verificationService->getVerificationHistory($content);

            // Assert: Should return exact number of verifications for this content
            $this->assertCount(
                $verificationsPerContent,
                $history,
                "Content {$content->id} should have exactly {$verificationsPerContent} verifications"
            );

            // Assert: All verifications should belong to this content
            foreach ($history as $verification) {
                $this->assertEquals(
                    $content->id,
                    $verification->content_id,
                    "Verification {$verification->id} should belong to content {$content->id}"
                );

                // Assert: Verification ID should be in the expected list
                $this->assertContains(
                    $verification->id,
                    $verificationsByContent[$content->id],
                    "Verification {$verification->id} should be in the expected list for content {$content->id}"
                );
            }
        }
    }

    /**
     * Test that content-specific history page displays only related verifications
     * 
     * @dataProvider contentHistoryPageProvider
     */
    public function test_content_specific_history_page_shows_only_related_verifications(
        int $targetContentVerifications,
        int $otherContentVerifications
    ): void {
        // Arrange: Create target content and other contents
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create target content with verifications
        $targetContent = Content::factory()->create([
            'judul' => 'Target Content',
            'skpd_id' => $skpd->id,
            'kategori_id' => $kategori->id,
            'publisher_id' => $publisher->id,
        ]);

        for ($i = 0; $i < $targetContentVerifications; $i++) {
            Verification::create([
                'content_id' => $targetContent->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'alasan' => "Target verification $i",
                'verified_at' => now()->subHours($i),
            ]);
        }

        // Create other content with verifications
        $otherContent = Content::factory()->create([
            'judul' => 'Other Content',
            'skpd_id' => $skpd->id,
            'kategori_id' => $kategori->id,
            'publisher_id' => $publisher->id,
        ]);

        for ($i = 0; $i < $otherContentVerifications; $i++) {
            Verification::create([
                'content_id' => $otherContent->id,
                'verifikator_id' => $operator->id,
                'status' => 'Rejected',
                'alasan' => "Other verification $i",
                'verified_at' => now()->subHours($i),
            ]);
        }

        // Act: Visit content-specific history page
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history', $targetContent->id));

        // Assert: Page should load successfully
        $response->assertStatus(200);

        // Assert: Should see target content title
        $response->assertSee('Target Content');

        // Assert: Should see target verifications
        for ($i = 0; $i < $targetContentVerifications; $i++) {
            $response->assertSee("Target verification $i");
        }

        // Assert: Should NOT see other content verifications
        for ($i = 0; $i < $otherContentVerifications; $i++) {
            $response->assertDontSee("Other verification $i");
        }
    }

    /**
     * Test that empty content history returns empty collection
     * 
     * @dataProvider emptyHistoryProvider
     */
    public function test_content_without_verifications_returns_empty_history(int $testIteration): void
    {
        // Arrange: Create content without verifications
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        $content = Content::factory()->create([
            'judul' => "Content without verifications $testIteration",
            'skpd_id' => $skpd->id,
            'kategori_id' => $kategori->id,
            'publisher_id' => $publisher->id,
            'status' => 'Pending',
        ]);

        // Act: Get verification history
        $history = $this->verificationService->getVerificationHistory($content);

        // Assert: Should return empty collection
        $this->assertCount(0, $history, 'Content without verifications should have empty history');
        $this->assertTrue($history->isEmpty(), 'History collection should be empty');
    }

    /**
     * Data providers
     */
    public static function contentHistoryProvider(): array
    {
        $testCases = [];
        
        // Generate 50 test cases with varying numbers of contents and verifications
        for ($i = 0; $i < 50; $i++) {
            $numContents = rand(2, 5);
            $verificationsPerContent = rand(1, 5);
            
            $testCases["history_$i"] = [$numContents, $verificationsPerContent];
        }
        
        return $testCases;
    }

    public static function contentHistoryPageProvider(): array
    {
        $testCases = [];
        
        // Generate 50 test cases
        for ($i = 0; $i < 50; $i++) {
            $targetVerifications = rand(1, 5);
            $otherVerifications = rand(1, 5);
            
            $testCases["page_$i"] = [$targetVerifications, $otherVerifications];
        }
        
        return $testCases;
    }

    public static function emptyHistoryProvider(): array
    {
        $testCases = [];
        
        // Generate 20 test cases for empty history
        for ($i = 0; $i < 20; $i++) {
            $testCases["empty_$i"] = [$i];
        }
        
        return $testCases;
    }
}
