<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Verification;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 12: Content approval state transition
 * 
 * *For any* content with status "Pending", when approved by Operator with a reason,
 * the status should change to "Approved" and a verification record should be created.
 * 
 * **Validates: Requirements 4.3**
 */
describe('Property 12: Content approval state transition', function () {
    
    beforeEach(function () {
        $this->verificationService = app(VerificationService::class);
    });

    /**
     * Property test: For any pending content approved by an operator,
     * the content status must change to "Approved" and a verification record must be created.
     */
    test('approving pending content changes status to Approved and creates verification record', function () {
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
            
            // Create operator
            $operator = User::factory()->create([
                'role' => 'Operator',
                'skpd_id' => null,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create pending content
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => Content::STATUS_PENDING,
            ]);
            
            // Generate random approval reason (can be null or a string)
            $reason = fake()->boolean(70) ? fake()->sentence() : null;
            
            // Verify initial state
            expect($content->status)->toBe(Content::STATUS_PENDING);
            expect($content->isPending())->toBeTrue();
            
            // Count verifications before approval
            $verificationCountBefore = Verification::where('content_id', $content->id)->count();
            
            // Approve content
            $verification = $this->verificationService->approveContent($content, $operator, $reason);
            
            // Refresh content from database
            $content->refresh();
            
            // Verify content status changed to Approved
            expect($content->status)->toBe(Content::STATUS_APPROVED,
                "Content status should be 'Approved' after approval, but got '{$content->status}'");
            expect($content->isApproved())->toBeTrue();
            
            // Verify verification record was created
            $verificationCountAfter = Verification::where('content_id', $content->id)->count();
            expect($verificationCountAfter)->toBe($verificationCountBefore + 1,
                "A new verification record should be created after approval");
            
            // Verify verification record details
            expect($verification)->toBeInstanceOf(Verification::class);
            expect($verification->content_id)->toBe($content->id);
            expect($verification->verifikator_id)->toBe($operator->id);
            expect($verification->status)->toBe(Verification::STATUS_APPROVED);
            expect($verification->alasan)->toBe($reason);
            expect($verification->verified_at)->not->toBeNull();
            
            // Verify verification is persisted in database
            $persistedVerification = Verification::find($verification->id);
            expect($persistedVerification)->not->toBeNull();
            expect($persistedVerification->status)->toBe(Verification::STATUS_APPROVED);
            
            // Clean up for next iteration
            Verification::where('content_id', $content->id)->delete();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Approving non-pending content should throw an exception.
     */
    test('approving non-pending content throws exception', function () {
        // Test with various non-pending statuses
        $nonPendingStatuses = [
            Content::STATUS_DRAFT,
            Content::STATUS_APPROVED,
            Content::STATUS_REJECTED,
            Content::STATUS_PUBLISHED,
        ];
        
        foreach ($nonPendingStatuses as $status) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create operator
            $operator = User::factory()->create([
                'role' => 'Operator',
                'skpd_id' => null,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create content with non-pending status
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => $status,
            ]);
            
            // Attempt to approve should throw exception
            expect(fn() => $this->verificationService->approveContent($content, $operator, 'Test reason'))
                ->toThrow(\InvalidArgumentException::class, 'Only pending content can be approved.');
            
            // Verify content status unchanged
            $content->refresh();
            expect($content->status)->toBe($status,
                "Content status should remain '{$status}' after failed approval attempt");
            
            // Verify no verification record was created
            $verificationCount = Verification::where('content_id', $content->id)->count();
            expect($verificationCount)->toBe(0,
                "No verification record should be created for failed approval attempt");
            
            // Clean up
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Verification record should correctly link to content and operator.
     */
    test('verification record correctly links to content and operator', function () {
        // Run 100 iterations
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
            
            // Create operator
            $operator = User::factory()->create([
                'role' => 'Operator',
                'skpd_id' => null,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create pending content
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'status' => Content::STATUS_PENDING,
            ]);
            
            $reason = fake()->sentence();
            
            // Approve content
            $verification = $this->verificationService->approveContent($content, $operator, $reason);
            
            // Verify relationships work correctly
            expect($verification->content->id)->toBe($content->id);
            expect($verification->verifikator->id)->toBe($operator->id);
            
            // Verify content can access its verifications
            $content->refresh();
            $contentVerifications = $content->verifications;
            expect($contentVerifications)->toHaveCount(1);
            expect($contentVerifications->first()->id)->toBe($verification->id);
            
            // Clean up
            Verification::where('content_id', $content->id)->delete();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });
});
