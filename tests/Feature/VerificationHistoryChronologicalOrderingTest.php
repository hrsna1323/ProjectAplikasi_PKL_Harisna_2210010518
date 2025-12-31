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
 * Feature: fix-verification-history-route, Property 2: Chronological ordering
 * 
 * Property: For any set of verification records retrieved, they should be ordered 
 * by verified_at timestamp in descending order (most recent first)
 * 
 * Validates: Requirements 1.3
 */
class VerificationHistoryChronologicalOrderingTest extends TestCase
{
    use RefreshDatabase;

    private VerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verificationService = app(VerificationService::class);
    }

    /**
     * Test that verification history is always ordered chronologically (most recent first)
     */
    public function test_verification_history_is_ordered_chronologically(): void
    {
        // Arrange: Create test data with random dates
        $operator = User::factory()->create(['role' => 'Operator']);
        $publisher = User::factory()->create(['role' => 'Publisher']);
        $skpd = Skpd::factory()->create();
        $kategori = KategoriKonten::factory()->create();

        // Create 10 verifications with random timestamps
        $verificationDates = [];
        for ($i = 0; $i < 10; $i++) {
            $randomDays = rand(0, 30);
            $date = now()->subDays($randomDays);
            $verificationDates[] = $date;

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
                'verified_at' => $date,
            ]);
        }

        // Act: Get all verification history
        $result = $this->verificationService->getAllVerificationHistory([]);

        // Assert: Verify chronological ordering (most recent first)
        $previousTimestamp = null;
        foreach ($result as $verification) {
            $currentTimestamp = $verification->verified_at->timestamp;
            
            if ($previousTimestamp !== null) {
                $this->assertGreaterThanOrEqual(
                    $currentTimestamp,
                    $previousTimestamp,
                    'Verifications should be ordered by most recent first (descending)'
                );
            }
            
            $previousTimestamp = $currentTimestamp;
        }

        // Additional assertion: First item should be the most recent
        if ($result->count() > 0) {
            $mostRecentDate = max($verificationDates);
            $firstVerificationDate = $result->first()->verified_at;
            
            $this->assertEquals(
                $mostRecentDate->format('Y-m-d H:i'),
                $firstVerificationDate->format('Y-m-d H:i'),
                'First verification should be the most recent'
            );
        }
    }

    /**
     * Data provider with various timestamp combinations
     * Generates at least 100 test cases with random dates
     */
    public static function verificationDataProvider(): array
    {
        $testCases = [];

        // Generate 100+ test cases with random dates
        for ($i = 0; $i < 100; $i++) {
            $numVerifications = rand(3, 10);
            $dates = [];
            
            for ($j = 0; $j < $numVerifications; $j++) {
                // Generate random dates within the last year
                $randomDays = rand(0, 365);
                $dates[] = now()->subDays($randomDays)->format('Y-m-d H:i:s');
            }
            
            $testCases["random_set_$i"] = [$dates];
        }

        // Add specific edge cases
        $testCases['same_timestamps'] = [
            [
                now()->format('Y-m-d H:i:s'),
                now()->format('Y-m-d H:i:s'),
                now()->format('Y-m-d H:i:s'),
            ]
        ];

        $testCases['sequential_dates'] = [
            [
                now()->subDays(5)->format('Y-m-d H:i:s'),
                now()->subDays(4)->format('Y-m-d H:i:s'),
                now()->subDays(3)->format('Y-m-d H:i:s'),
                now()->subDays(2)->format('Y-m-d H:i:s'),
                now()->subDays(1)->format('Y-m-d H:i:s'),
            ]
        ];

        $testCases['reverse_sequential_dates'] = [
            [
                now()->subDays(1)->format('Y-m-d H:i:s'),
                now()->subDays(2)->format('Y-m-d H:i:s'),
                now()->subDays(3)->format('Y-m-d H:i:s'),
                now()->subDays(4)->format('Y-m-d H:i:s'),
                now()->subDays(5)->format('Y-m-d H:i:s'),
            ]
        ];

        return $testCases;
    }
}
