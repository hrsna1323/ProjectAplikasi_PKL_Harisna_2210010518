<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Verification;
use App\Models\KategoriKonten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: fix-verification-history-route, Property 4: Content detail link presence
 * 
 * Property: For any verification record displayed, the rendered output should include 
 * a clickable link to view that content's details
 * 
 * Validates: Requirements 1.5
 */
class VerificationContentDetailLinkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that each verification record has a link to content details
     * 
     * @dataProvider multipleVerificationsProvider
     */
    public function test_each_verification_has_content_detail_link(int $numVerifications): void
    {
        // Arrange: Create multiple verifications
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        $contentIds = [];
        for ($i = 0; $i < $numVerifications; $i++) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => $i % 2 === 0 ? 'Approved' : 'Rejected',
                'verified_at' => now()->subDays($i),
            ]);

            $contentIds[] = $content->id;
        }

        // Act: Visit the verification history page
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert: Each content should have a detail link
        foreach ($contentIds as $contentId) {
            $detailUrl = route('operator.verification.show', $contentId);
            $response->assertSee($detailUrl, false); // false = don't escape HTML
        }

        // Assert: "Lihat Detail" button text should appear for each verification
        $response->assertSee('Lihat Detail');
    }

    /**
     * Test that content detail links are clickable and navigate correctly
     * 
     * @dataProvider singleVerificationProvider
     */
    public function test_content_detail_link_is_clickable_and_navigates_correctly(
        string $contentTitle,
        string $status
    ): void {
        // Arrange: Create a verification
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        $content = Content::factory()->create([
            'judul' => $contentTitle,
            'skpd_id' => $skpd->id,
            'kategori_id' => $kategori->id,
            'publisher_id' => $publisher->id,
            'status' => $status,
        ]);

        Verification::create([
            'content_id' => $content->id,
            'verifikator_id' => $operator->id,
            'status' => $status,
            'verified_at' => now(),
        ]);

        // Act: Visit history page and click the detail link
        $this->actingAs($operator);
        
        $historyResponse = $this->get(route('operator.verification.history.index'));
        $historyResponse->assertStatus(200);

        // Follow the link to content details
        $detailResponse = $this->get(route('operator.verification.show', $content->id));

        // Assert: Detail page should load successfully
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($contentTitle);
    }

    /**
     * Test that link is present even when content has no URL
     * 
     * @dataProvider contentWithoutUrlProvider
     */
    public function test_detail_link_present_even_without_content_url(int $numContents): void
    {
        // Arrange: Create verifications for content without URLs
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        for ($i = 0; $i < $numContents; $i++) {
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'kategori_id' => $kategori->id,
                'publisher_id' => $publisher->id,
                'url_konten' => null, // No URL
            ]);

            Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => 'Approved',
                'verified_at' => now(),
            ]);
        }

        // Act: Visit the verification history page
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert: Detail links should still be present
        $response->assertStatus(200);
        
        // Count occurrences of "Lihat Detail" button
        $content = $response->getContent();
        $detailLinkCount = substr_count($content, 'Lihat Detail');
        
        $this->assertGreaterThanOrEqual(
            $numContents,
            $detailLinkCount,
            "Should have at least {$numContents} detail links"
        );
    }

    /**
     * Data providers
     */
    public static function multipleVerificationsProvider(): array
    {
        $testCases = [];
        
        // Generate 50 test cases with varying numbers of verifications
        for ($i = 0; $i < 50; $i++) {
            $numVerifications = rand(1, 10);
            $testCases["verifications_$i"] = [$numVerifications];
        }
        
        return $testCases;
    }

    public static function singleVerificationProvider(): array
    {
        $testCases = [];
        
        $titles = [
            'Berita Terbaru',
            'Pengumuman Penting',
            'Informasi Publik',
            'Laporan Kegiatan',
            'Artikel Daerah',
        ];

        $statuses = ['Approved', 'Rejected'];

        // Generate 50 test cases
        for ($i = 0; $i < 50; $i++) {
            $title = $titles[array_rand($titles)] . " $i";
            $status = $statuses[array_rand($statuses)];
            
            $testCases["single_$i"] = [$title, $status];
        }
        
        return $testCases;
    }

    public static function contentWithoutUrlProvider(): array
    {
        $testCases = [];
        
        // Generate test cases with varying numbers of content
        for ($i = 1; $i <= 20; $i++) {
            $testCases["no_url_$i"] = [$i];
        }
        
        return $testCases;
    }
}
