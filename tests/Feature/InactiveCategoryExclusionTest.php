<?php

use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 23: Inactive category exclusion
 * 
 * *For any* category with is_active = false, it should not appear in the category 
 * selection for new content, but should still be visible for existing content.
 * 
 * **Validates: Requirements 9.5**
 */
describe('Property 23: Inactive category exclusion', function () {

    /**
     * Property test: For any category with is_active = false, it should NOT appear
     * in the active() scope query used for new content selection.
     */
    test('inactive categories are excluded from active scope', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a random mix of active and inactive categories
            $activeCount = fake()->numberBetween(1, 5);
            $inactiveCount = fake()->numberBetween(1, 5);
            
            // Create active categories
            $activeCategories = [];
            for ($j = 0; $j < $activeCount; $j++) {
                $category = KategoriKonten::create([
                    'nama_kategori' => 'Active_' . uniqid() . '_' . $j,
                    'deskripsi' => fake()->sentence(),
                    'is_active' => true,
                ]);
                $activeCategories[] = $category;
            }
            $activeCategoryIds = array_map(fn($c) => $c->id, $activeCategories);
            
            // Create inactive categories
            $inactiveCategories = [];
            for ($j = 0; $j < $inactiveCount; $j++) {
                $category = KategoriKonten::create([
                    'nama_kategori' => 'Inactive_' . uniqid() . '_' . $j,
                    'deskripsi' => fake()->sentence(),
                    'is_active' => false,
                ]);
                $inactiveCategories[] = $category;
            }
            $inactiveCategoryIds = array_map(fn($c) => $c->id, $inactiveCategories);
            
            // All category IDs created in this iteration
            $allIterationIds = array_merge($activeCategoryIds, $inactiveCategoryIds);
            
            // Query using active scope - filter to only this iteration's categories
            $availableCategories = KategoriKonten::active()
                ->whereIn('id', $allIterationIds)
                ->get();
            $availableCategoryIds = $availableCategories->pluck('id')->toArray();
            
            // Verify: All returned categories should have is_active = true
            foreach ($availableCategories as $category) {
                expect($category->is_active)->toBeTrue(
                    "Category returned by active() scope should have is_active = true"
                );
            }
            
            // Verify: No inactive categories should be in the result
            foreach ($inactiveCategoryIds as $inactiveCategoryId) {
                expect($availableCategoryIds)->not->toContain($inactiveCategoryId,
                    "Inactive category should NOT appear in active() scope"
                );
            }
            
            // Verify: All active categories should be in the result
            foreach ($activeCategoryIds as $activeCategoryId) {
                expect(in_array($activeCategoryId, $availableCategoryIds))->toBeTrue(
                    "Active category ID {$activeCategoryId} should appear in active() scope"
                );
            }
            
            // Verify count matches
            expect(count($availableCategoryIds))->toBe($activeCount,
                "Should return exactly the number of active categories created"
            );
            
            // Clean up for next iteration
            DB::table('kategori_konten')->whereIn('id', $allIterationIds)->delete();
        }
    });

    /**
     * Property test: For any existing content with an inactive category,
     * the category should still be visible/retrievable via the content relationship.
     */
    test('existing content retains visibility of inactive category', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create an active category
            $category = KategoriKonten::factory()->create(['is_active' => true]);
            $categoryId = $category->id;
            $categoryName = $category->nama_kategori;
            
            // Create content with this category
            $content = Content::factory()->create([
                'skpd_id' => $skpd->id,
                'publisher_id' => $publisher->id,
                'kategori_id' => $category->id,
            ]);
            $contentId = $content->id;
            
            // Now deactivate the category
            $category->update(['is_active' => false]);
            
            // Refresh content from database
            $freshContent = Content::with('kategori')->find($contentId);
            
            // Verify: Content still has the category relationship
            expect($freshContent->kategori)->not->toBeNull(
                "Content should still have access to its category even after deactivation"
            );
            
            // Verify: The category is correctly retrieved
            expect($freshContent->kategori->id)->toBe($categoryId,
                "Content's category ID should match the original category"
            );
            
            // Verify: The category is indeed inactive
            expect($freshContent->kategori->is_active)->toBeFalse(
                "The category should be marked as inactive"
            );
            
            // Verify: Category name is still accessible
            expect($freshContent->kategori->nama_kategori)->toBe($categoryName,
                "Category name should still be accessible for existing content"
            );
            
            // Clean up for next iteration
            Content::destroy($contentId);
            User::destroy($publisher->id);
            Skpd::destroy($skpd->id);
            KategoriKonten::destroy($categoryId);
        }
    });

    /**
     * Property test: For any category that transitions from active to inactive,
     * it should be excluded from new content selection but remain for existing content.
     */
    test('category state transition maintains data integrity', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD and publisher
            $skpd = Skpd::factory()->create(['status' => 'Active']);
            $publisher = User::factory()->create([
                'role' => 'Publisher',
                'skpd_id' => $skpd->id,
            ]);
            
            // Create multiple categories (some will be deactivated)
            $categoryCount = fake()->numberBetween(3, 6);
            $categories = KategoriKonten::factory()
                ->count($categoryCount)
                ->create(['is_active' => true]);
            
            $categoryIds = $categories->pluck('id')->toArray();
            
            // Create content for each category
            $contentIds = [];
            foreach ($categories as $category) {
                $content = Content::factory()->create([
                    'skpd_id' => $skpd->id,
                    'publisher_id' => $publisher->id,
                    'kategori_id' => $category->id,
                ]);
                $contentIds[] = $content->id;
            }
            
            // Randomly deactivate some categories (at least 1, but not all)
            $deactivateCount = fake()->numberBetween(1, $categoryCount - 1);
            $categoriesToDeactivate = $categories->take($deactivateCount);
            $deactivatedIds = [];
            
            foreach ($categoriesToDeactivate as $category) {
                $category->update(['is_active' => false]);
                $deactivatedIds[] = $category->id;
            }
            
            // Get available categories for new content using active scope
            // Filter to only categories from this iteration
            $availableForNewContent = KategoriKonten::active()
                ->whereIn('id', $categoryIds)
                ->pluck('id')
                ->toArray();
            
            // Verify: Deactivated categories are NOT available for new content
            foreach ($deactivatedIds as $deactivatedId) {
                expect($availableForNewContent)->not->toContain($deactivatedId,
                    "Deactivated category should not be available for new content"
                );
            }
            
            // Verify: All existing content still has access to their categories
            foreach ($contentIds as $contentId) {
                $freshContent = Content::with('kategori')->find($contentId);
                expect($freshContent->kategori)->not->toBeNull(
                    "Existing content should still have access to its category"
                );
                expect($freshContent->kategori_id)->toBe($freshContent->kategori->id,
                    "Content's kategori_id should match the related category"
                );
            }
            
            // Clean up for next iteration
            Content::destroy($contentIds);
            User::destroy($publisher->id);
            Skpd::destroy($skpd->id);
            KategoriKonten::destroy($categoryIds);
        }
    });
});
