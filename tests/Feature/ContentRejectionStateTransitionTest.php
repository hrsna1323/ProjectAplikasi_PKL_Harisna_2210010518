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
 * Feature: manajemen-konten-skpd, Property 13: Content rejection state transition
 * 
 * *For any* content with status "Pending", when rejected by Operator with a reason,
 * the status should change to "Rejected" and a verification record should be created.
 * 
 * **Validates: Requirements 4.4**
 */
describe('Property 13: Content rejection state transition', function () {
    
    beforeEach(function () {
        $this->verificationService = app(VerificationService::class);
    });

    /**
     * Property test: For any pending content rejected by an operator with a reason,
     * the content status must change to "Rejected" and a verification record must be created.
     */
    test('rejecting pending content changes status to Rejected and creates verification record', function () {
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
            
            // Generate random rejection reason (must be non-empty)
            $reason = fake()->sentence();
            
            // Verify initial state
            expect($content->status)->toBe(Content::STATUS_PENDING);
            expect($content->isPending())->toBeTrue();
            
            // Count verifications before rejection
            $verificationCountBefore = Verification::where('content_id', $content->id)->count();
            
            // Reject content
            $verification = $this->verificationService->rejectContent($content, $operator, $reason);
            
            // Refresh content from database
            $content->refresh();
            
            // Verify content status changed to Rejected
            expect($content->status)->toBe(Content::STATUS_REJECTED,
                "Content status should be 'Rejected' after rejection, but got '{$content->status}'");
            
            // Verify verification record was created
            $verificationCountAfter = Verification::where('content_id', $content->id)->count();
            expect($verificationCountAfter)->toBe($verificationCountBefore + 1,
                "A new verification record should be created after rejection");
            
            // Verify verification record details
            expect($verification)->toBeInstanceOf(Verification::class);
            expect($verification->content_id)->toBe($content->id);
            expect($verification->verifikator_id)->toBe($operator->id);
            expect($verification->status)->toBe(Verification::STATUS_REJECTED);
            expect($verification->alasan)->toBe($reason);
            expect($verification->verified_at)->not->toBeNull();
            
            // Verify verification is persisted in database
            $persistedVerification = Verification::find($verification->id);
            expect($persistedVerification)->not->toBeNull();
            expect($persistedVerification->status)->toBe(Verification::STATUS_REJECTED);
            
            // Clean up for next iteration
            Verification::where('content_id', $content->id)->delete();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Rejecting non-pending content should throw an exception.
     */
    test('rejecting non-pending content throws exception', function () {
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
            
            // Attempt to reject should throw exception
            expect(fn() => $this->verificationService->rejectContent($content, $operator, 'Test reason'))
                ->toThrow(\InvalidArgumentException::class, 'Only pending content can be rejected.');
            
            // Verify content status unchanged
            $content->refresh();
            expect($content->status)->toBe($status,
                "Content status should remain '{$status}' after failed rejection attempt");
            
            // Verify no verification record was created
            $verificationCount = Verification::where('content_id', $content->id)->count();
            expect($verificationCount)->toBe(0,
                "No verification record should be created for failed rejection attempt");
            
            // Clean up
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: Rejection requires a non-empty reason.
     */
    test('rejecting content without reason throws exception', function () {
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
        
        // Test with empty string
        expect(fn() => $this->verificationService->rejectContent($content, $operator, ''))
            ->toThrow(\InvalidArgumentException::class, 'Reason is required for rejection.');
        
        // Test with whitespace only
        expect(fn() => $this->verificationService->rejectContent($content, $operator, '   '))
            ->toThrow(\InvalidArgumentException::class, 'Reason is required for rejection.');
        
        // Verify content status unchanged
        $content->refresh();
        expect($content->status)->toBe(Content::STATUS_PENDING,
            "Content status should remain 'Pending' after failed rejection attempt");
        
        // Verify no verification record was created
        $verificationCount = Verification::where('content_id', $content->id)->count();
        expect($verificationCount)->toBe(0,
            "No verification record should be created for failed rejection attempt");
        
        // Clean up
        Content::where('id', $content->id)->delete();
        User::whereIn('id', [$publisher->id, $operator->id])->delete();
        $skpd->delete();
        $kategori->delete();
    });

    /**
     * Property test: Verification record correctly links to content and operator.
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
            
            // Reject content
            $verification = $this->verificationService->rejectContent($content, $operator, $reason);
            
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
