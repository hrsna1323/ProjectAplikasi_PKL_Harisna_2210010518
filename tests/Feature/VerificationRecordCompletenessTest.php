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
 * Feature: fix-verification-history-route, Property 1: Verification record completeness
 * 
 * Property: For any verification record displayed on the general history page, 
 * the rendered output should contain the content title, SKPD name, verification action, 
 * operator name, and timestamp
 * 
 * Validates: Requirements 1.2
 */
class VerificationRecordCompletenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that verification history page displays all required fields
     * 
     * @dataProvider verificationRecordProvider
     */
    public function test_verification_record_contains_all_required_fields(
        string $contentTitle,
        string $skpdName,
        string $operatorName,
        string $status
    ): void {
        // Arrange: Create verification with specific data
        $operator = User::factory()->create([
            'role' => 'Operator',
            'name' => $operatorName,
        ]);

        $publisher = User::factory()->create(['role' => 'Publisher']);

        $skpd = Skpd::factory()->create([
            'nama_skpd' => $skpdName,
        ]);

        $kategori = KategoriKonten::factory()->create([
            'nama_kategori' => 'Test Category',
        ]);

        $content = Content::factory()->create([
            'judul' => $contentTitle,
            'skpd_id' => $skpd->id,
            'kategori_id' => $kategori->id,
            'publisher_id' => $publisher->id,
            'status' => $status,
        ]);

        $verification = Verification::create([
            'content_id' => $content->id,
            'verifikator_id' => $operator->id,
            'status' => $status,
            'verified_at' => now(),
        ]);

        // Act: Visit the verification history page as operator
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert: Response should be successful
        $response->assertStatus(200);

        // Assert: All required fields should be present in the rendered output
        $response->assertSee($contentTitle); // Content title
        $response->assertSee($skpdName); // SKPD name
        $response->assertSee($status); // Verification action (Approved/Rejected)
        $response->assertSee($operatorName); // Operator name
        $response->assertSee($kategori->nama_kategori); // Category name
        
        // Assert: Timestamp should be present (check for date format)
        $formattedDate = $verification->verified_at->format('d M Y');
        $response->assertSee($formattedDate);
    }

    /**
     * Test that verification history page includes link to content details
     * 
     * @dataProvider verificationRecordProvider
     */
    public function test_verification_record_includes_content_detail_link(
        string $contentTitle,
        string $skpdName,
        string $operatorName,
        string $status
    ): void {
        // Arrange: Create verification
        $operator = User::factory()->create([
            'role' => 'Operator',
            'name' => $operatorName,
        ]);

        $publisher = User::factory()->create(['role' => 'Publisher']);

        $skpd = Skpd::factory()->create([
            'nama_skpd' => $skpdName,
        ]);

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

        // Act: Visit the verification history page
        $this->actingAs($operator);
        $response = $this->get(route('operator.verification.history.index'));

        // Assert: Link to content details should be present
        $detailUrl = route('operator.verification.show', $content->id);
        $response->assertSee($detailUrl);
        $response->assertSee('Lihat Detail');
    }

    /**
     * Data provider with various verification record combinations
     * Generates 100+ test cases
     */
    public static function verificationRecordProvider(): array
    {
        $testCases = [];

        $contentTitles = [
            'Berita Terbaru SKPD',
            'Pengumuman Penting',
            'Informasi Publik',
            'Laporan Kegiatan',
            'Artikel Daerah',
            'Sosialisasi Program',
            'Kegiatan Masyarakat',
            'Pembangunan Infrastruktur',
        ];

        $skpdNames = [
            'Dinas Pendidikan',
            'Dinas Kesehatan',
            'Dinas Pekerjaan Umum',
            'Dinas Sosial',
            'Bappeda',
            'Dinas Perhubungan',
            'Dinas Lingkungan Hidup',
        ];

        $operatorNames = [
            'Ahmad Operator',
            'Budi Verifikator',
            'Citra Admin',
            'Dewi Operator',
            'Eko Verifikator',
        ];

        $statuses = ['Approved', 'Rejected'];

        // Generate 100+ combinations
        for ($i = 0; $i < 100; $i++) {
            $contentTitle = $contentTitles[array_rand($contentTitles)];
            $skpdName = $skpdNames[array_rand($skpdNames)];
            $operatorName = $operatorNames[array_rand($operatorNames)];
            $status = $statuses[array_rand($statuses)];

            $testCases["combination_$i"] = [
                $contentTitle,
                $skpdName,
                $operatorName,
                $status,
            ];
        }

        return $testCases;
    }
}
