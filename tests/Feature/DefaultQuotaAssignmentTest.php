<?php

use App\Models\Skpd;
use App\Services\ActivityLogService;
use App\Services\SkpdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 4: Default quota assignment
 * 
 * *For any* SKPD created without explicit quota value, the system should assign 
 * the default quota of 3 konten per month.
 * 
 * **Validates: Requirements 2.2**
 */
describe('Property 4: Default quota assignment', function () {
    
    beforeEach(function () {
        $this->activityLogService = Mockery::mock(ActivityLogService::class);
        $this->activityLogService->shouldReceive('logUserAction')->andReturn(null);
        $this->activityLogService->shouldReceive('logSkpdUpdated')->andReturn(null);
        
        $this->skpdService = new SkpdService($this->activityLogService);
    });

    /**
     * Property test: For any SKPD created without explicit kuota_bulanan,
     * the default value should be 3.
     */
    test('SKPD created without explicit quota gets default value of 3', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random SKPD data WITHOUT kuota_bulanan
            $data = [
                'nama_skpd' => fake()->company(),
                'website_url' => fake()->url(),
                'email' => fake()->companyEmail(),
                'status' => 'Active',
            ];
            
            // Create SKPD using the service (which should apply default)
            $skpd = $this->skpdService->createSkpd($data);
            
            // Verify default quota is 3
            expect($skpd->kuota_bulanan)->toBe(3, 
                "SKPD created without explicit quota should have kuota_bulanan = 3, got {$skpd->kuota_bulanan}");
            
            // Clean up for next iteration
            $skpd->delete();
        }
    });

    /**
     * Property test: For any SKPD created with explicit kuota_bulanan,
     * the provided value should be used instead of default.
     */
    test('SKPD created with explicit quota uses provided value', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate random quota value (different from default 3)
            $explicitQuota = fake()->numberBetween(1, 20);
            
            // Generate random SKPD data WITH explicit kuota_bulanan
            $data = [
                'nama_skpd' => fake()->company(),
                'website_url' => fake()->url(),
                'email' => fake()->companyEmail(),
                'kuota_bulanan' => $explicitQuota,
                'status' => 'Active',
            ];
            
            // Create SKPD using the service
            $skpd = $this->skpdService->createSkpd($data);
            
            // Verify the explicit quota is used
            expect($skpd->kuota_bulanan)->toBe($explicitQuota, 
                "SKPD created with explicit quota {$explicitQuota} should have that value, got {$skpd->kuota_bulanan}");
            
            // Clean up for next iteration
            $skpd->delete();
        }
    });

    /**
     * Property test: For any SKPD created directly via Model without kuota_bulanan,
     * the model's default attribute should apply (value = 3).
     */
    test('SKPD model default attribute assigns quota of 3', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create SKPD directly via Model without kuota_bulanan
            $skpd = Skpd::create([
                'nama_skpd' => fake()->company(),
                'website_url' => fake()->url(),
                'email' => fake()->companyEmail(),
                'status' => 'Active',
            ]);
            
            // Verify default quota is 3 from model's $attributes
            expect($skpd->kuota_bulanan)->toBe(3, 
                "SKPD created via Model without quota should have kuota_bulanan = 3, got {$skpd->kuota_bulanan}");
            
            // Clean up for next iteration
            $skpd->delete();
        }
    });

    /**
     * Property test: For any SKPD, the database default should also be 3.
     * This tests the migration default value.
     */
    test('database default for kuota_bulanan is 3', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Insert directly to database without specifying kuota_bulanan
            // This bypasses model defaults and tests database default
            $id = \Illuminate\Support\Facades\DB::table('skpd')->insertGetId([
                'nama_skpd' => fake()->company(),
                'website_url' => fake()->url(),
                'email' => fake()->companyEmail(),
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Retrieve the record
            $skpd = Skpd::find($id);
            
            // Verify database default is 3
            expect($skpd->kuota_bulanan)->toBe(3, 
                "Database default for kuota_bulanan should be 3, got {$skpd->kuota_bulanan}");
            
            // Clean up for next iteration
            $skpd->delete();
        }
    });
});
