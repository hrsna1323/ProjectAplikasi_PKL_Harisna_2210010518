<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Notification;
use App\Models\Skpd;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 15: Notification on content verification
 * 
 * *For any* content that is verified (approved or rejected), a notification should be 
 * created for the Publisher who created the content.
 * 
 * **Validates: Requirements 4.5, 7.2**
 */
describe('Property 15: Notification on content verification', function () {
    
    beforeEach(function () {
        $this->verificationService = app(VerificationService::class);
    });

    /**
     * Property test: For any pending content that is approved,
     * a notification should be created for the Publisher who created the content.
     */
    test('content approval creates notification for the publisher', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create([
                'is_active' => true,
            ]);
            
            // Create operator who will verify
            $operator = User::factory()->operator()->create([
                'is_active' => true,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create pending content
            $content = Content::factory()->pending()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'judul' => fake()->sentence(),
            ]);
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Generate random approval reason (optional)
            $reason = fake()->boolean() ? fake()->sentence() : null;
            
            // Approve content via service
            $verification = $this->verificationService->approveContent($content, $operator, $reason);
            
            // Verify notification was created for the publisher
            $notification = Notification::where('type', 'content_verified')
                ->where('related_content_id', $content->id)
                ->where('user_id', $publisher->id)
                ->first();
            
            expect($notification)->not->toBeNull(
                "Publisher should receive a notification when content is approved");
            expect($notification->is_read)->toBeFalse(
                "Notification should be unread by default");
            expect($notification->related_content_id)->toBe($content->id,
                "Notification should reference the verified content");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->forceDelete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any pending content that is rejected,
     * a notification should be created for the Publisher who created the content.
     */
    test('content rejection creates notification for the publisher', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create([
                'is_active' => true,
            ]);
            
            // Create operator who will verify
            $operator = User::factory()->operator()->create([
                'is_active' => true,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create pending content
            $content = Content::factory()->pending()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'judul' => fake()->sentence(),
            ]);
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Generate random rejection reason (required)
            $reason = fake()->sentence();
            
            // Reject content via service
            $verification = $this->verificationService->rejectContent($content, $operator, $reason);
            
            // Verify notification was created for the publisher
            $notification = Notification::where('type', 'content_verified')
                ->where('related_content_id', $content->id)
                ->where('user_id', $publisher->id)
                ->first();
            
            expect($notification)->not->toBeNull(
                "Publisher should receive a notification when content is rejected");
            expect($notification->is_read)->toBeFalse(
                "Notification should be unread by default");
            expect($notification->related_content_id)->toBe($content->id,
                "Notification should reference the verified content");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->forceDelete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content verification (approved or rejected),
     * the notification message should contain the content title and verification status.
     */
    test('notification message contains content title and verification status', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create([
                'is_active' => true,
            ]);
            
            // Create operator who will verify
            $operator = User::factory()->operator()->create([
                'is_active' => true,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content title
            $contentTitle = fake()->sentence();
            
            // Create pending content
            $content = Content::factory()->pending()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
                'judul' => $contentTitle,
            ]);
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Randomly choose to approve or reject
            $shouldApprove = fake()->boolean();
            
            if ($shouldApprove) {
                $reason = fake()->boolean() ? fake()->sentence() : null;
                $verification = $this->verificationService->approveContent($content, $operator, $reason);
                $expectedStatusText = 'disetujui';
            } else {
                $reason = fake()->sentence();
                $verification = $this->verificationService->rejectContent($content, $operator, $reason);
                $expectedStatusText = 'ditolak';
            }
            
            // Get the notification
            $notification = Notification::where('type', 'content_verified')
                ->where('related_content_id', $content->id)
                ->where('user_id', $publisher->id)
                ->first();
            
            expect($notification)->not->toBeNull("Notification should be created");
            
            // Verify notification message contains content title
            $containsTitle = str_contains($notification->message, $contentTitle);
            expect($containsTitle)->toBeTrue(
                "Notification message should contain content title. Message: '{$notification->message}', Title: '{$contentTitle}'");
            
            // Verify notification message contains status text
            $containsStatus = str_contains($notification->message, $expectedStatusText);
            expect($containsStatus)->toBeTrue(
                "Notification message should contain status text '{$expectedStatusText}'. Message: '{$notification->message}'");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->forceDelete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content verification, exactly one notification
     * should be created for the publisher (not multiple).
     */
    test('exactly one notification is created per verification', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create([
                'is_active' => true,
            ]);
            
            // Create operator who will verify
            $operator = User::factory()->operator()->create([
                'is_active' => true,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Create pending content
            $content = Content::factory()->pending()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $kategori->id,
            ]);
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Randomly choose to approve or reject
            if (fake()->boolean()) {
                $reason = fake()->boolean() ? fake()->sentence() : null;
                $this->verificationService->approveContent($content, $operator, $reason);
            } else {
                $reason = fake()->sentence();
                $this->verificationService->rejectContent($content, $operator, $reason);
            }
            
            // Count notifications for this content verification
            $notificationCount = Notification::where('type', 'content_verified')
                ->where('related_content_id', $content->id)
                ->count();
            
            expect($notificationCount)->toBe(1,
                "Exactly one notification should be created per verification, got {$notificationCount}");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->forceDelete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });
});
