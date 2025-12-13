<?php

use App\Models\Skpd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 2: Publisher-SKPD association
 * 
 * *For any* Publisher user created, the user must be associated with exactly one SKPD.
 * 
 * **Validates: Requirements 1.5**
 */
describe('Property 2: Publisher-SKPD association', function () {

    /**
     * Property test: For any Publisher user, they must have exactly one SKPD association.
     * This means skpd_id must be non-null and reference a valid SKPD.
     */
    test('publisher user must be associated with exactly one SKPD', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a random SKPD
            $skpd = Skpd::factory()->create();
            
            // Create a Publisher user with the SKPD
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Verify the publisher has role 'Publisher'
            expect($publisher->role)->toBe('Publisher',
                "User created with publisher() factory should have role 'Publisher'");
            
            // Verify the publisher has a non-null skpd_id
            expect($publisher->skpd_id)->not->toBeNull(
                "Publisher must have a non-null skpd_id");
            
            // Verify the skpd_id references the correct SKPD
            expect($publisher->skpd_id)->toBe($skpd->id,
                "Publisher's skpd_id should match the assigned SKPD");
            
            // Verify the relationship works correctly
            expect($publisher->skpd)->not->toBeNull(
                "Publisher's skpd relationship should return the SKPD");
            expect($publisher->skpd->id)->toBe($skpd->id,
                "Publisher's skpd relationship should return the correct SKPD");
            
            // Verify isPublisher() method returns true
            expect($publisher->isPublisher())->toBeTrue(
                "Publisher's isPublisher() method should return true");
            
            // Clean up for next iteration
            $publisher->delete();
            $skpd->delete();
        }
    });

    /**
     * Property test: For any Publisher created without explicit SKPD,
     * the factory should automatically create and associate an SKPD.
     */
    test('publisher factory auto-creates SKPD when none provided', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a Publisher without providing an SKPD
            $publisher = User::factory()->publisher()->create();
            
            // Verify the publisher has a non-null skpd_id
            expect($publisher->skpd_id)->not->toBeNull(
                "Publisher created without explicit SKPD should have auto-created SKPD");
            
            // Verify the SKPD exists in database
            $skpd = Skpd::find($publisher->skpd_id);
            expect($skpd)->not->toBeNull(
                "Publisher's skpd_id should reference an existing SKPD");
            
            // Verify the relationship works
            expect($publisher->skpd)->not->toBeNull(
                "Publisher's skpd relationship should return the auto-created SKPD");
            
            // Clean up for next iteration
            $skpdId = $publisher->skpd_id;
            $publisher->delete();
            Skpd::find($skpdId)?->delete();
        }
    });

    /**
     * Property test: For any Admin or Operator user, they should NOT have an SKPD association.
     * This ensures only Publishers are associated with SKPDs.
     */
    test('admin and operator users should not have SKPD association', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create Admin user
            $admin = User::factory()->admin()->create();
            
            // Verify Admin has null skpd_id
            expect($admin->skpd_id)->toBeNull(
                "Admin user should have null skpd_id");
            expect($admin->role)->toBe('Admin',
                "Admin user should have role 'Admin'");
            expect($admin->isAdmin())->toBeTrue(
                "Admin's isAdmin() method should return true");
            expect($admin->isPublisher())->toBeFalse(
                "Admin's isPublisher() method should return false");
            
            // Create Operator user
            $operator = User::factory()->operator()->create();
            
            // Verify Operator has null skpd_id
            expect($operator->skpd_id)->toBeNull(
                "Operator user should have null skpd_id");
            expect($operator->role)->toBe('Operator',
                "Operator user should have role 'Operator'");
            expect($operator->isOperator())->toBeTrue(
                "Operator's isOperator() method should return true");
            expect($operator->isPublisher())->toBeFalse(
                "Operator's isPublisher() method should return false");
            
            // Clean up for next iteration
            $admin->delete();
            $operator->delete();
        }
    });

    /**
     * Property test: For any Publisher, the SKPD relationship should be bidirectional.
     * The SKPD should list the Publisher in its users relationship.
     */
    test('publisher-SKPD relationship is bidirectional', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create an SKPD
            $skpd = Skpd::factory()->create();
            
            // Create random number of publishers for this SKPD (1-5)
            $numPublishers = fake()->numberBetween(1, 5);
            $publishers = [];
            
            for ($j = 0; $j < $numPublishers; $j++) {
                $publisher = User::factory()->publisher($skpd)->create();
                $publishers[] = $publisher;
            }
            
            // Verify SKPD has the correct number of publishers
            $skpdPublishers = $skpd->users()->where('role', 'Publisher')->get();
            expect($skpdPublishers->count())->toBe($numPublishers,
                "SKPD should have exactly {$numPublishers} publishers");
            
            // Verify each publisher is in the SKPD's users
            foreach ($publishers as $publisher) {
                $found = $skpdPublishers->contains('id', $publisher->id);
                expect($found)->toBeTrue(
                    "Publisher {$publisher->id} should be in SKPD's users relationship");
                
                // Verify the reverse relationship
                expect($publisher->skpd->id)->toBe($skpd->id,
                    "Publisher's skpd should reference the correct SKPD");
            }
            
            // Clean up for next iteration
            foreach ($publishers as $publisher) {
                $publisher->delete();
            }
            $skpd->delete();
        }
    });

    /**
     * Property test: For any Publisher, changing their SKPD should update the association.
     */
    test('publisher SKPD association can be updated', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create two SKPDs
            $skpd1 = Skpd::factory()->create();
            $skpd2 = Skpd::factory()->create();
            
            // Create a Publisher with first SKPD
            $publisher = User::factory()->publisher($skpd1)->create();
            
            // Verify initial association
            expect($publisher->skpd_id)->toBe($skpd1->id,
                "Publisher should initially be associated with SKPD1");
            
            // Update to second SKPD
            $publisher->skpd_id = $skpd2->id;
            $publisher->save();
            $publisher->refresh();
            
            // Verify updated association
            expect($publisher->skpd_id)->toBe($skpd2->id,
                "Publisher should now be associated with SKPD2");
            expect($publisher->skpd->id)->toBe($skpd2->id,
                "Publisher's skpd relationship should return SKPD2");
            
            // Verify SKPD1 no longer has this publisher
            $skpd1Publishers = $skpd1->users()->where('role', 'Publisher')->get();
            expect($skpd1Publishers->contains('id', $publisher->id))->toBeFalse(
                "SKPD1 should no longer have this publisher");
            
            // Verify SKPD2 now has this publisher
            $skpd2Publishers = $skpd2->users()->where('role', 'Publisher')->get();
            expect($skpd2Publishers->contains('id', $publisher->id))->toBeTrue(
                "SKPD2 should now have this publisher");
            
            // Clean up for next iteration
            $publisher->delete();
            $skpd1->delete();
            $skpd2->delete();
        }
    });
});
