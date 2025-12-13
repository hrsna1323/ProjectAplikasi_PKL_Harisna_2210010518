<?php

use App\Models\ActivityLog;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\SkpdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 21: SKPD change tracking
 * 
 * *For any* SKPD update, if field values change, the activity log should record
 * the old value, new value, and the user who made the change.
 * 
 * **Validates: Requirements 8.3**
 */
describe('Property 21: SKPD change tracking', function () {
    
    beforeEach(function () {
        $this->activityLogService = app(ActivityLogService::class);
        $this->skpdService = app(SkpdService::class);
    });

    /**
     * Property test: For any SKPD update with field changes,
     * the activity log should record old_value, new_value, and user who made the change.
     * 
     * Validates: Requirements 8.3
     */
    test('SKPD update records old value, new value, and user in activity log', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create admin user who will make the change
            $admin = User::factory()->create([
                'role' => 'Admin',
                'skpd_id' => null,
            ]);
            
            // Authenticate as admin
            $this->actingAs($admin);
            
            // Create SKPD with initial random values
            $originalNama = fake()->company();
            $originalWebsite = fake()->url();
            $originalEmail = fake()->email();
            $originalKuota = fake()->numberBetween(1, 10);
            
            $skpd = Skpd::factory()->create([
                'nama_skpd' => $originalNama,
                'website_url' => $originalWebsite,
                'email' => $originalEmail,
                'kuota_bulanan' => $originalKuota,
                'status' => 'Active',
            ]);
            
            // Generate new values that are different from original
            $newNama = fake()->company() . ' - Updated';
            $newKuota = $originalKuota + fake()->numberBetween(1, 5);
            
            // Update SKPD using service
            $updateData = [
                'nama_skpd' => $newNama,
                'kuota_bulanan' => $newKuota,
            ];
            
            $this->skpdService->updateSkpd($skpd, $updateData);
            
            // Find the activity log for this SKPD update
            $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                ->where('user_id', $admin->id)
                ->latest()
                ->first();
            
            // Property assertion 1: Activity log must exist for SKPD update
            expect($activityLog)->not->toBeNull(
                "Activity log entry must exist for SKPD update");
            
            // Property assertion 2: Activity log must record the user who made the change
            expect($activityLog->user_id)->toBe($admin->id,
                "Activity log must record the user who made the change");
            
            // Property assertion 3: Activity log must have old_value recorded
            expect($activityLog->old_value)->not->toBeNull(
                "Activity log must record old values when fields change");
            
            // Property assertion 4: Activity log must have new_value recorded
            expect($activityLog->new_value)->not->toBeNull(
                "Activity log must record new values when fields change");
            
            // Decode and verify the tracked changes
            $oldValues = json_decode($activityLog->old_value, true);
            $newValues = json_decode($activityLog->new_value, true);
            
            // Property assertion 5: Old values must be valid JSON array
            expect($oldValues)->toBeArray(
                "Old values must be a valid JSON array");
            
            // Property assertion 6: New values must be valid JSON array
            expect($newValues)->toBeArray(
                "New values must be a valid JSON array");
            
            // Property assertion 7: Changed fields must be tracked in old_value
            expect(array_key_exists('nama_skpd', $oldValues))->toBeTrue(
                "Changed field 'nama_skpd' must be tracked in old_value");
            expect(array_key_exists('kuota_bulanan', $oldValues))->toBeTrue(
                "Changed field 'kuota_bulanan' must be tracked in old_value");
            
            // Property assertion 8: Changed fields must be tracked in new_value
            expect(array_key_exists('nama_skpd', $newValues))->toBeTrue(
                "Changed field 'nama_skpd' must be tracked in new_value");
            expect(array_key_exists('kuota_bulanan', $newValues))->toBeTrue(
                "Changed field 'kuota_bulanan' must be tracked in new_value");
            
            // Property assertion 9: Old values must match original values
            expect($oldValues['nama_skpd'])->toBe($originalNama,
                "Old value for nama_skpd must match original value");
            expect($oldValues['kuota_bulanan'])->toBe($originalKuota,
                "Old value for kuota_bulanan must match original value");
            
            // Property assertion 10: New values must match updated values
            expect($newValues['nama_skpd'])->toBe($newNama,
                "New value for nama_skpd must match updated value");
            expect($newValues['kuota_bulanan'])->toBe($newKuota,
                "New value for kuota_bulanan must match updated value");
            
            // Clean up for next iteration
            ActivityLog::where('user_id', $admin->id)->delete();
            $skpd->delete();
            $admin->delete();
        }
    });

    /**
     * Property test: For any SKPD update where no fields actually change,
     * no activity log should be created.
     * 
     * Validates: Requirements 8.3 (only log actual changes)
     */
    test('SKPD update with no actual changes does not create activity log', function () {
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
            
            // Count activity logs before update
            $logCountBefore = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                ->where('user_id', $admin->id)
                ->count();
            
            // Update SKPD with same values (no actual change)
            $updateData = [
                'nama_skpd' => $skpd->nama_skpd,
                'kuota_bulanan' => $skpd->kuota_bulanan,
            ];
            
            $this->skpdService->updateSkpd($skpd, $updateData);
            
            // Count activity logs after update
            $logCountAfter = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                ->where('user_id', $admin->id)
                ->count();
            
            // Property assertion: No activity log should be created when no fields change
            expect($logCountAfter)->toBe($logCountBefore,
                "No activity log should be created when no fields actually change");
            
            // Clean up for next iteration
            ActivityLog::where('user_id', $admin->id)->delete();
            $skpd->delete();
            $admin->delete();
        }
    });

    /**
     * Property test: For any single field change in SKPD,
     * only that field should be tracked in old_value and new_value.
     * 
     * Validates: Requirements 8.3
     */
    test('single field change tracks only that field', function () {
        // Define trackable fields
        $trackableFields = ['nama_skpd', 'website_url', 'email', 'kuota_bulanan', 'status'];
        
        // Run iterations for each trackable field
        foreach ($trackableFields as $field) {
            for ($i = 0; $i < 20; $i++) {
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
                
                // Store original value
                $originalValue = $skpd->{$field};
                
                // Generate new value based on field type
                $newValue = match($field) {
                    'nama_skpd' => fake()->company() . ' - Changed',
                    'website_url' => fake()->url() . '/changed',
                    'email' => 'changed_' . fake()->email(),
                    'kuota_bulanan' => $originalValue + fake()->numberBetween(1, 5),
                    'status' => $originalValue === 'Active' ? 'Inactive' : 'Active',
                };
                
                // Update only this single field
                $updateData = [$field => $newValue];
                
                $this->skpdService->updateSkpd($skpd, $updateData);
                
                // Find the activity log
                $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                    ->where('user_id', $admin->id)
                    ->latest()
                    ->first();
                
                expect($activityLog)->not->toBeNull(
                    "Activity log should exist for single field change: {$field}");
                
                // Decode values
                $oldValues = json_decode($activityLog->old_value, true);
                $newValues = json_decode($activityLog->new_value, true);
                
                // Property assertion: Only the changed field should be tracked
                expect(count($oldValues))->toBe(1,
                    "Only one field should be tracked in old_value when single field changes");
                expect(count($newValues))->toBe(1,
                    "Only one field should be tracked in new_value when single field changes");
                
                // Property assertion: The tracked field should be the changed field
                expect(array_key_exists($field, $oldValues))->toBeTrue(
                    "The changed field '{$field}' should be tracked in old_value");
                expect(array_key_exists($field, $newValues))->toBeTrue(
                    "The changed field '{$field}' should be tracked in new_value");
                
                // Property assertion: Values should match
                expect($oldValues[$field])->toBe($originalValue,
                    "Old value for '{$field}' should match original");
                expect($newValues[$field])->toBe($newValue,
                    "New value for '{$field}' should match updated value");
                
                // Clean up
                ActivityLog::where('user_id', $admin->id)->delete();
                $skpd->delete();
                $admin->delete();
            }
        }
    });

    /**
     * Property test: For any SKPD update, the detail field should contain
     * human-readable description of the changes.
     * 
     * Validates: Requirements 8.3
     */
    test('activity log detail contains human-readable change description', function () {
        // Run 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Create admin user
            $admin = User::factory()->create([
                'role' => 'Admin',
                'skpd_id' => null,
            ]);
            
            // Authenticate as admin
            $this->actingAs($admin);
            
            // Create SKPD
            $skpd = Skpd::factory()->create([
                'nama_skpd' => fake()->company(),
                'status' => 'Active',
            ]);
            
            $originalNama = $skpd->nama_skpd;
            $newNama = fake()->company() . ' - Updated';
            
            // Update SKPD
            $this->skpdService->updateSkpd($skpd, ['nama_skpd' => $newNama]);
            
            // Find the activity log
            $activityLog = ActivityLog::where('action_type', ActivityLog::ACTION_SKPD_UPDATED)
                ->where('user_id', $admin->id)
                ->latest()
                ->first();
            
            expect($activityLog)->not->toBeNull();
            
            // Property assertion: Detail should not be empty
            expect($activityLog->detail)->not->toBeEmpty(
                "Activity log detail should not be empty");
            
            // Property assertion: Detail should contain SKPD name (the updated name)
            $updatedSkpdName = $skpd->fresh()->nama_skpd;
            expect(str_contains($activityLog->detail, $updatedSkpdName))->toBeTrue(
                "Activity log detail should contain SKPD name: '{$updatedSkpdName}'");
            
            // Property assertion: Detail should indicate it's an update
            expect(
                str_contains(strtolower($activityLog->detail), 'diperbarui') ||
                str_contains(strtolower($activityLog->detail), 'update') ||
                str_contains(strtolower($activityLog->detail), 'perubahan')
            )->toBeTrue("Activity log detail should indicate an update action");
            
            // Clean up
            ActivityLog::where('user_id', $admin->id)->delete();
            $skpd->delete();
            $admin->delete();
        }
    });
});
