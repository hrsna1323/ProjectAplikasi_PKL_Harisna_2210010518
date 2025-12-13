<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Notification;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 14: Notification on content submission
 * 
 * *For any* content created by Publisher, a notification should be created for all Operator users.
 * 
 * **Validates: Requirements 3.2, 7.1**
 */
describe('Property 14: Notification on content submission', function () {
    
    beforeEach(function () {
        $this->contentService = app(ContentService::class);
    });

    /**
     * Property test: For any content created via ContentService::createContent(),
     * notifications should be created for ALL active Operator users.
     */
    test('content submission creates notifications for all active operators', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random number of operators (1-5)
            $operatorCount = fake()->numberBetween(1, 5);
            
            // Create active operators
            $operators = [];
            for ($j = 0; $j < $operatorCount; $j++) {
                $operators[] = User::factory()->operator()->create([
                    'is_active' => true,
                ]);
            }
            
            // Create some inactive operators (should NOT receive notifications)
            $inactiveOperatorCount = fake()->numberBetween(0, 2);
            $inactiveOperators = [];
            for ($j = 0; $j < $inactiveOperatorCount; $j++) {
                $inactiveOperators[] = User::factory()->operator()->create([
                    'is_active' => false,
                ]);
            }
            
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content data
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Create content via service
            $content = $this->contentService->createContent($contentData, $publisher);
            
            // Verify notifications were created for ALL active operators
            $notifications = Notification::where('type', 'content_submitted')
                ->where('related_content_id', $content->id)
                ->get();
            
            expect($notifications->count())->toBe($operatorCount,
                "Expected {$operatorCount} notifications for active operators, got {$notifications->count()}");
            
            // Verify each active operator received a notification
            foreach ($operators as $operator) {
                $operatorNotification = $notifications->where('user_id', $operator->id)->first();
                expect($operatorNotification)->not->toBeNull(
                    "Active operator {$operator->id} should receive a notification");
                expect($operatorNotification->is_read)->toBeFalse(
                    "Notification should be unread by default");
                expect($operatorNotification->related_content_id)->toBe($content->id,
                    "Notification should reference the created content");
            }
            
            // Verify inactive operators did NOT receive notifications
            foreach ($inactiveOperators as $inactiveOperator) {
                $inactiveNotification = $notifications->where('user_id', $inactiveOperator->id)->first();
                expect($inactiveNotification)->toBeNull(
                    "Inactive operator {$inactiveOperator->id} should NOT receive a notification");
            }
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', array_merge(
                [$publisher->id],
                array_map(fn($o) => $o->id, $operators),
                array_map(fn($o) => $o->id, $inactiveOperators)
            ))->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content submission, notification message should contain
     * content title and SKPD name.
     */
    test('notification message contains content title and SKPD name', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create an operator
            $operator = User::factory()->operator()->create([
                'is_active' => true,
            ]);
            
            // Create SKPD with random name
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content data with specific title
            $contentTitle = fake()->sentence();
            $contentData = [
                'judul' => $contentTitle,
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Create content via service
            $content = $this->contentService->createContent($contentData, $publisher);
            
            // Get the notification
            $notification = Notification::where('type', 'content_submitted')
                ->where('related_content_id', $content->id)
                ->where('user_id', $operator->id)
                ->first();
            
            expect($notification)->not->toBeNull("Notification should be created");
            
            // Verify notification message contains content title
            // The message format is: "Konten baru '{title}' dari SKPD {skpd_name} menunggu verifikasi."
            $containsTitle = str_contains($notification->message, $contentTitle);
            expect($containsTitle)->toBeTrue(
                "Notification message should contain content title. Message: '{$notification->message}', Title: '{$contentTitle}'");
            
            // Verify notification message contains SKPD name
            $containsSkpdName = str_contains($notification->message, $skpd->nama_skpd);
            expect($containsSkpdName)->toBeTrue(
                "Notification message should contain SKPD name. Message: '{$notification->message}', SKPD: '{$skpd->nama_skpd}'");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: When no active operators exist, content submission should still succeed
     * but no notifications should be created.
     */
    test('content submission succeeds even when no active operators exist', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create only inactive operators (or no operators at all)
            $inactiveOperatorCount = fake()->numberBetween(0, 3);
            $inactiveOperators = [];
            for ($j = 0; $j < $inactiveOperatorCount; $j++) {
                $inactiveOperators[] = User::factory()->operator()->create([
                    'is_active' => false,
                ]);
            }
            
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'status' => 'Active',
            ]);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Generate random content data
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];
            
            // Clear any existing notifications
            Notification::truncate();
            
            // Create content via service - should not throw exception
            $content = $this->contentService->createContent($contentData, $publisher);
            
            // Verify content was created successfully
            expect($content)->not->toBeNull("Content should be created successfully");
            expect($content->id)->toBeGreaterThan(0, "Content should have valid ID");
            
            // Verify no notifications were created for inactive operators
            $notifications = Notification::where('type', 'content_submitted')
                ->where('related_content_id', $content->id)
                ->get();
            
            expect($notifications->count())->toBe(0,
                "No notifications should be created when no active operators exist");
            
            // Clean up for next iteration
            Notification::truncate();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', array_merge(
                [$publisher->id],
                array_map(fn($o) => $o->id, $inactiveOperators)
            ))->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });
});
