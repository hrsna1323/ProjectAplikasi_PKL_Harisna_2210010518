<?php

use App\Models\ActivityLog;
use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Verification;
use App\Services\ActivityLogService;
use App\Services\ContentService;
use App\Services\SkpdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 20: Audit trail completeness
 * 
 * *For any* important user action (content creation, verification, SKPD update),
 * an activity log entry must be created with user_id, action_type, detail, and timestamp.
 * 
 * **Validates: Requirements 2.3, 3.4, 8.1, 8.2, 8.3**
 */
describe('Property 20: Audit trail completeness', function () {
    
    beforeEach(function () {
        $this->activityLogService = app(ActivityLogService::class);
        $this->contentService = app(ContentService::class);
        $this->skpdService = app(SkpdService::class);
    });

    /**
     * Property test: For any content creation, an activity log entry must be created
     * with user_id, action_type, detail, and timestamp.
     * 
     * Validates: Requirements 3.4, 8.1
     */
    test('content creation creates activity log with required fields', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            
            // Create publisher for this SKPD
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create category
            $kategori = KategoriKonten::factory()->create();
            
            // Count activity logs before content creation
            $logCountBefore = ActivityLog::count();
            
            // Create content data
            $contentData = [
                'judul' => fake()->sentence(),
                'deskripsi' => fake()->paragraph(),
                'kategori_id' => $kategori->id,
                'url_publikasi' => fake()->url(),
                'tanggal_publikasi' => fake()->date(),
            ];
            
            // Create content using service
            $content = $this->contentService->createContent($contentData, $publisher);
            
            // Verify activity log was created
            $logCountAfter = ActivityLog::count();
            expect($logCountAfter)->toBeGreaterThan($logCountBefore,
                "Activity log should be created after content creation");
            
            // Find the activity log for this content creation
            $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_CONTENT_CREATED)
                ->where('user_id', $publisher->id)
                ->latest()
                ->first();
            
            expect($activityLog)->not->toBeNull(
                "Activity log entry should exist for content creation");
            
            // Verify required fields are present
            expect($activityLog->user_id)->toBe($publisher->id,
                "Activity log should have correct user_id");
            expect($activityLog->action_type)->toBe(ActivityLog::ACTION_CONTENT_CREATED,
                "Activity log should have correct action_type");
            expect($activityLog->detail)->not->toBeEmpty(
                "Activity log should have non-empty detail");
            expect($activityLog->created_at)->not->toBeNull(
                "Activity log should have timestamp (created_at)");
            
            // Verify detail contains content title
            expect($activityLog->detail)->toContain($content->judul);
            
            // Clean up for next iteration
            ActivityLog::where('user_id', $publisher->id)->delete();
            Content::where('id', $content->id)->delete();
            $publisher->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any content verification (approved or rejected),
     * an activity log entry must be created with user_id, action_type, detail, and timestamp.
     * 
     * Validates: Requirements 8.2
     */
    test('content verification creates activity log with required fields', function () {
        // Run 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            
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
            
            // Count activity logs before verification
            $logCountBefore = ActivityLog::count();
            
            // Generate random reason
            $reason = fake()->sentence();
            
            // Randomly choose to approve or reject
            $isApproval = fake()->boolean();
            
            // Create verification record and log
            $verification = Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => $isApproval ? Verification::STATUS_APPROVED : Verification::STATUS_REJECTED,
                'alasan' => $reason,
                'verified_at' => now(),
            ]);
            
            // Update content status
            $content->update([
                'status' => $isApproval ? Content::STATUS_APPROVED : Content::STATUS_REJECTED
            ]);
            
            // Log the verification using ActivityLogService
            $this->activityLogService->logContentVerified($content, $verification, $operator);
            
            // Verify activity log was created
            $logCountAfter = ActivityLog::count();
            expect($logCountAfter)->toBeGreaterThan($logCountBefore,
                "Activity log should be created after content verification");
            
            // Find the activity log for this verification
            $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_CONTENT_VERIFIED)
                ->where('user_id', $operator->id)
                ->latest()
                ->first();
            
            expect($activityLog)->not->toBeNull(
                "Activity log entry should exist for content verification");
            
            // Verify required fields are present
            expect($activityLog->user_id)->toBe($operator->id,
                "Activity log should have correct user_id");
            expect($activityLog->action_type)->toBe(ActivityLog::ACTION_CONTENT_VERIFIED,
                "Activity log should have correct action_type");
            expect($activityLog->detail)->not->toBeEmpty(
                "Activity log should have non-empty detail");
            expect($activityLog->created_at)->not->toBeNull(
                "Activity log should have timestamp (created_at)");
            
            // Verify old_value and new_value are recorded for state transition
            expect($activityLog->old_value)->not->toBeNull(
                "Activity log should record old status value");
            expect($activityLog->new_value)->not->toBeNull(
                "Activity log should record new status value");
            
            // Clean up for next iteration
            ActivityLog::where('user_id', $operator->id)->delete();
            Verification::where('content_id', $content->id)->delete();
            Content::where('id', $content->id)->delete();
            User::whereIn('id', [$publisher->id, $operator->id])->delete();
            $skpd->delete();
            $kategori->delete();
        }
    });

    /**
     * Property test: For any SKPD update with field changes,
     * an activity log entry must be created with old_value, new_value, and user who made the change.
     * 
     * Validates: Requirements 2.3, 8.3
     */
    test('SKPD update creates activity log with old and new values', function () {
        // Run 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Create admin user
            $admin = User::factory()->create([
                'role' => 'Admin',
                'skpd_id' => null,
            ]);
            
            // Authenticate as admin
            $this->actingAs($admin);
            
            // Create SKPD with initial values
            $skpd = Skpd::factory()->create([
                'nama_skpd' => fake()->company(),
                'website_url' => fake()->url(),
                'email' => fake()->email(),
                'kuota_bulanan' => fake()->numberBetween(1, 10),
                'status' => 'Active',
            ]);
            
            // Store original values
            $originalNama = $skpd->nama_skpd;
            $originalKuota = $skpd->kuota_bulanan;
            
            // Count activity logs before update
            $logCountBefore = ActivityLog::count();
            
            // Generate new values that are different
            $newNama = fake()->company() . ' Updated';
            $newKuota = $originalKuota + fake()->numberBetween(1, 5);
            
            // Update SKPD using service
            $updateData = [
                'nama_skpd' => $newNama,
                'kuota_bulanan' => $newKuota,
            ];
            
            $this->skpdService->updateSkpd($skpd, $updateData);
            
            // Verify activity log was created
            $logCountAfter = ActivityLog::count();
            expect($logCountAfter)->toBeGreaterThan($logCountBefore,
                "Activity log should be created after SKPD update");
            
            // Find the activity log for this SKPD update
            $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                ->where('user_id', $admin->id)
                ->latest()
                ->first();
            
            expect($activityLog)->not->toBeNull(
                "Activity log entry should exist for SKPD update");
            
            // Verify required fields are present
            expect($activityLog->user_id)->toBe($admin->id,
                "Activity log should have correct user_id");
            expect($activityLog->action_type)->toBe(ActivityLog::ACTION_SKPD_UPDATED,
                "Activity log should have correct action_type");
            expect($activityLog->detail)->not->toBeEmpty(
                "Activity log should have non-empty detail");
            expect($activityLog->created_at)->not->toBeNull(
                "Activity log should have timestamp (created_at)");
            
            // Verify old_value and new_value contain the changes
            expect($activityLog->old_value)->not->toBeNull(
                "Activity log should record old values");
            expect($activityLog->new_value)->not->toBeNull(
                "Activity log should record new values");
            
            // Decode and verify old/new values
            $oldValues = json_decode($activityLog->old_value, true);
            $newValues = json_decode($activityLog->new_value, true);
            
            expect($oldValues)->toBeArray();
            expect($newValues)->toBeArray();
            
            // Verify the changed fields are tracked
            expect($oldValues['nama_skpd'])->toBe($originalNama,
                "Old value should contain original nama_skpd");
            expect($newValues['nama_skpd'])->toBe($newNama,
                "New value should contain updated nama_skpd");
            expect($oldValues['kuota_bulanan'])->toBe($originalKuota,
                "Old value should contain original kuota_bulanan");
            expect($newValues['kuota_bulanan'])->toBe($newKuota,
                "New value should contain updated kuota_bulanan");
            
            // Clean up for next iteration
            ActivityLog::where('user_id', $admin->id)->delete();
            $skpd->delete();
            $admin->delete();
        }
    });

    /**
     * Property test: Activity log entries must always have user_id, action_type, and timestamp.
     * 
     * Validates: Requirements 8.1
     */
    test('all activity log entries have required fields', function () {
        // Create various users
        $admin = User::factory()->create(['role' => 'Admin', 'skpd_id' => null]);
        $operator = User::factory()->create(['role' => 'Operator', 'skpd_id' => null]);
        
        $skpd = Skpd::factory()->create(['status' => 'Active']);
        $publisher = User::factory()->create(['role' => 'Publisher', 'skpd_id' => $skpd->id]);
        
        // Run 100 iterations creating various log entries
        for ($i = 0; $i < 100; $i++) {
            // Randomly select a user
            $user = fake()->randomElement([$admin, $operator, $publisher]);
            
            // Randomly select an action type
            $actionType = fake()->randomElement([
                ActivityLog::ACTION_CONTENT_CREATED,
                ActivityLog::ACTION_CONTENT_VERIFIED,
                ActivityLog::ACTION_SKPD_UPDATED,
                ActivityLog::ACTION_USER_LOGIN,
            ]);
            
            // Create activity log entry
            $activityLog = $this->activityLogService->logUserAction(
                $actionType,
                fake()->sentence(),
                $user,
                fake()->boolean() ? fake()->word() : null,
                fake()->boolean() ? fake()->word() : null
            );
            
            // Verify required fields are always present
            expect($activityLog->user_id)->not->toBeNull(
                "Activity log must have user_id");
            expect($activityLog->action_type)->not->toBeNull(
                "Activity log must have action_type");
            expect($activityLog->action_type)->not->toBeEmpty(
                "Activity log action_type must not be empty");
            expect($activityLog->created_at)->not->toBeNull(
                "Activity log must have timestamp (created_at)");
            
            // Verify user relationship works
            expect($activityLog->user)->not->toBeNull(
                "Activity log should have valid user relationship");
            expect($activityLog->user->id)->toBe($user->id,
                "Activity log user relationship should match user_id");
        }
        
        // Clean up
        ActivityLog::whereIn('user_id', [$admin->id, $operator->id, $publisher->id])->delete();
        User::whereIn('id', [$admin->id, $operator->id, $publisher->id])->delete();
        $skpd->delete();
    });
});
