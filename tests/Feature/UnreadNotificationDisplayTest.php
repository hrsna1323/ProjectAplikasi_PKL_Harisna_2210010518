<?php

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 16: Unread notification display
 * 
 * *For any* user, when viewing dashboard, only notifications with is_read = false 
 * should appear in the notification list.
 * 
 * **Validates: Requirements 7.5**
 */
describe('Property 16: Unread notification display', function () {
    
    beforeEach(function () {
        $this->notificationService = app(NotificationService::class);
    });

    /**
     * Property test: For any user with mixed read/unread notifications,
     * getUnreadNotifications() should return ONLY unread notifications.
     */
    test('getUnreadNotifications returns only unread notifications', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a user (any role)
            $roles = ['Admin', 'Operator', 'Publisher'];
            $role = fake()->randomElement($roles);
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);
            
            // Generate random number of unread notifications (1-10)
            $unreadCount = fake()->numberBetween(1, 10);
            
            // Generate random number of read notifications (0-10)
            $readCount = fake()->numberBetween(0, 10);
            
            // Create unread notifications
            $unreadNotifications = [];
            for ($j = 0; $j < $unreadCount; $j++) {
                $unreadNotifications[] = Notification::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement([
                        Notification::TYPE_CONTENT_SUBMITTED,
                        Notification::TYPE_CONTENT_APPROVED,
                        Notification::TYPE_CONTENT_REJECTED,
                        Notification::TYPE_QUOTA_REMINDER,
                        Notification::TYPE_QUOTA_WARNING,
                    ]),
                    'message' => fake()->sentence(),
                    'is_read' => false,
                ]);
            }
            
            // Create read notifications
            $readNotifications = [];
            for ($j = 0; $j < $readCount; $j++) {
                $readNotifications[] = Notification::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement([
                        Notification::TYPE_CONTENT_SUBMITTED,
                        Notification::TYPE_CONTENT_APPROVED,
                        Notification::TYPE_CONTENT_REJECTED,
                        Notification::TYPE_QUOTA_REMINDER,
                        Notification::TYPE_QUOTA_WARNING,
                    ]),
                    'message' => fake()->sentence(),
                    'is_read' => true,
                ]);
            }
            
            // Get unread notifications via service
            $result = $this->notificationService->getUnreadNotifications($user);
            
            // Verify count matches expected unread count
            expect($result->count())->toBe($unreadCount,
                "Expected {$unreadCount} unread notifications, got {$result->count()}");
            
            // Verify ALL returned notifications have is_read = false
            foreach ($result as $notification) {
                expect($notification->is_read)->toBeFalse(
                    "All returned notifications should have is_read = false");
            }
            
            // Verify NO read notifications are included
            $readIds = array_map(fn($n) => $n->id, $readNotifications);
            foreach ($result as $notification) {
                expect(in_array($notification->id, $readIds))->toBeFalse(
                    "Read notifications should NOT be included in unread list");
            }
            
            // Clean up for next iteration
            Notification::where('user_id', $user->id)->delete();
            $user->delete();
        }
    });

    /**
     * Property test: For any user with only read notifications,
     * getUnreadNotifications() should return an empty collection.
     */
    test('getUnreadNotifications returns empty when all notifications are read', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a user (any role)
            $roles = ['Admin', 'Operator', 'Publisher'];
            $role = fake()->randomElement($roles);
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);
            
            // Generate random number of read notifications (1-10)
            $readCount = fake()->numberBetween(1, 10);
            
            // Create only read notifications
            for ($j = 0; $j < $readCount; $j++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement([
                        Notification::TYPE_CONTENT_SUBMITTED,
                        Notification::TYPE_CONTENT_APPROVED,
                        Notification::TYPE_CONTENT_REJECTED,
                    ]),
                    'message' => fake()->sentence(),
                    'is_read' => true,
                ]);
            }
            
            // Get unread notifications via service
            $result = $this->notificationService->getUnreadNotifications($user);
            
            // Verify result is empty
            expect($result->count())->toBe(0,
                "Expected 0 unread notifications when all are read, got {$result->count()}");
            
            // Clean up for next iteration
            Notification::where('user_id', $user->id)->delete();
            $user->delete();
        }
    });

    /**
     * Property test: For any user, getUnreadNotifications() should only return
     * notifications belonging to that specific user.
     */
    test('getUnreadNotifications returns only notifications for the specific user', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create target user
            $targetUser = User::factory()->create([
                'role' => fake()->randomElement(['Admin', 'Operator', 'Publisher']),
                'is_active' => true,
            ]);
            
            // Create other users (1-3)
            $otherUserCount = fake()->numberBetween(1, 3);
            $otherUsers = [];
            for ($j = 0; $j < $otherUserCount; $j++) {
                $otherUsers[] = User::factory()->create([
                    'role' => fake()->randomElement(['Admin', 'Operator', 'Publisher']),
                    'is_active' => true,
                ]);
            }
            
            // Create unread notifications for target user
            $targetUnreadCount = fake()->numberBetween(1, 5);
            for ($j = 0; $j < $targetUnreadCount; $j++) {
                Notification::create([
                    'user_id' => $targetUser->id,
                    'type' => Notification::TYPE_CONTENT_SUBMITTED,
                    'message' => fake()->sentence(),
                    'is_read' => false,
                ]);
            }
            
            // Create unread notifications for other users
            foreach ($otherUsers as $otherUser) {
                $otherUnreadCount = fake()->numberBetween(1, 5);
                for ($j = 0; $j < $otherUnreadCount; $j++) {
                    Notification::create([
                        'user_id' => $otherUser->id,
                        'type' => Notification::TYPE_CONTENT_SUBMITTED,
                        'message' => fake()->sentence(),
                        'is_read' => false,
                    ]);
                }
            }
            
            // Get unread notifications for target user
            $result = $this->notificationService->getUnreadNotifications($targetUser);
            
            // Verify count matches target user's unread count
            expect($result->count())->toBe($targetUnreadCount,
                "Expected {$targetUnreadCount} notifications for target user, got {$result->count()}");
            
            // Verify ALL returned notifications belong to target user
            foreach ($result as $notification) {
                expect($notification->user_id)->toBe($targetUser->id,
                    "All returned notifications should belong to target user");
            }
            
            // Clean up for next iteration
            Notification::whereIn('user_id', array_merge(
                [$targetUser->id],
                array_map(fn($u) => $u->id, $otherUsers)
            ))->delete();
            User::whereIn('id', array_merge(
                [$targetUser->id],
                array_map(fn($u) => $u->id, $otherUsers)
            ))->delete();
        }
    });

    /**
     * Property test: The Notification model's unread() scope should filter
     * correctly for any set of notifications.
     */
    test('Notification unread scope filters correctly', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a user
            $user = User::factory()->create([
                'role' => fake()->randomElement(['Admin', 'Operator', 'Publisher']),
                'is_active' => true,
            ]);
            
            // Generate random counts
            $unreadCount = fake()->numberBetween(0, 10);
            $readCount = fake()->numberBetween(0, 10);
            
            // Create unread notifications
            for ($j = 0; $j < $unreadCount; $j++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_CONTENT_SUBMITTED,
                    'message' => fake()->sentence(),
                    'is_read' => false,
                ]);
            }
            
            // Create read notifications
            for ($j = 0; $j < $readCount; $j++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_CONTENT_SUBMITTED,
                    'message' => fake()->sentence(),
                    'is_read' => true,
                ]);
            }
            
            // Use the unread scope
            $unreadNotifications = Notification::forUser($user->id)->unread()->get();
            
            // Verify count
            expect($unreadNotifications->count())->toBe($unreadCount,
                "Unread scope should return {$unreadCount} notifications, got {$unreadNotifications->count()}");
            
            // Verify all have is_read = false
            foreach ($unreadNotifications as $notification) {
                expect($notification->is_read)->toBeFalse(
                    "All notifications from unread scope should have is_read = false");
            }
            
            // Clean up for next iteration
            Notification::where('user_id', $user->id)->delete();
            $user->delete();
        }
    });

    /**
     * Property test: After marking a notification as read, it should no longer
     * appear in unread notifications list.
     */
    test('marking notification as read removes it from unread list', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a user
            $user = User::factory()->create([
                'role' => fake()->randomElement(['Admin', 'Operator', 'Publisher']),
                'is_active' => true,
            ]);
            
            // Create multiple unread notifications (2-5)
            $unreadCount = fake()->numberBetween(2, 5);
            $notifications = [];
            for ($j = 0; $j < $unreadCount; $j++) {
                $notifications[] = Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_CONTENT_SUBMITTED,
                    'message' => fake()->sentence(),
                    'is_read' => false,
                ]);
            }
            
            // Verify initial unread count
            $initialUnread = $this->notificationService->getUnreadNotifications($user);
            expect($initialUnread->count())->toBe($unreadCount);
            
            // Pick a random notification to mark as read
            $notificationToMark = fake()->randomElement($notifications);
            $this->notificationService->markAsRead($notificationToMark->id);
            
            // Get unread notifications again
            $afterMarkRead = $this->notificationService->getUnreadNotifications($user);
            
            // Verify count decreased by 1
            expect($afterMarkRead->count())->toBe($unreadCount - 1,
                "Unread count should decrease by 1 after marking one as read");
            
            // Verify the marked notification is not in the list
            $markedNotificationInList = $afterMarkRead->contains('id', $notificationToMark->id);
            expect($markedNotificationInList)->toBeFalse(
                "Marked notification should not appear in unread list");
            
            // Clean up for next iteration
            Notification::where('user_id', $user->id)->delete();
            $user->delete();
        }
    });
});
