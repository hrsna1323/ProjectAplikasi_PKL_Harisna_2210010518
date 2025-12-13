<?php

use App\Models\Skpd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 1: Role-based access control
 * 
 * *For any* user with a specific role (Admin, Operator, or Publisher), when accessing 
 * system features, the system should grant or deny access according to the role's 
 * defined permissions.
 * 
 * **Validates: Requirements 1.2, 1.3, 1.4, 10.3**
 */
describe('Property 1: Role-based access control', function () {

    /**
     * Property test: For any Admin user, they should have access to Admin routes
     * and be denied access to Operator and Publisher specific routes.
     */
    test('admin users can access admin routes and are denied access to other role routes', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create an Admin user
            $admin = User::factory()->admin()->create();
            
            // Verify Admin can access Admin dashboard
            $response = $this->actingAs($admin)->get('/admin/dashboard');
            expect($response->status())->toBe(200,
                "Admin should have access to admin dashboard (iteration {$i})");
            
            // Verify Admin can access SKPD management
            $response = $this->actingAs($admin)->get('/admin/skpd');
            expect($response->status())->toBe(200,
                "Admin should have access to SKPD management (iteration {$i})");
            
            // Verify Admin is denied access to Operator routes
            $response = $this->actingAs($admin)->get('/operator/dashboard');
            expect($response->status())->toBe(403,
                "Admin should be denied access to operator dashboard (iteration {$i})");
            
            // Verify Admin is denied access to Publisher routes
            $response = $this->actingAs($admin)->get('/publisher/dashboard');
            expect($response->status())->toBe(403,
                "Admin should be denied access to publisher dashboard (iteration {$i})");
            
            // Clean up for next iteration
            $admin->delete();
        }
    });

    /**
     * Property test: For any Operator user, they should have access to Operator routes
     * and be denied access to Admin and Publisher specific routes.
     */
    test('operator users can access operator routes and are denied access to other role routes', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create an Operator user
            $operator = User::factory()->operator()->create();
            
            // Verify Operator can access Operator dashboard
            $response = $this->actingAs($operator)->get('/operator/dashboard');
            expect($response->status())->toBe(200,
                "Operator should have access to operator dashboard (iteration {$i})");
            
            // Verify Operator can access verification queue
            $response = $this->actingAs($operator)->get('/operator/verification');
            expect($response->status())->toBe(200,
                "Operator should have access to verification queue (iteration {$i})");
            
            // Verify Operator can access monitoring
            $response = $this->actingAs($operator)->get('/operator/monitoring');
            expect($response->status())->toBe(200,
                "Operator should have access to monitoring (iteration {$i})");
            
            // Verify Operator is denied access to Admin routes
            $response = $this->actingAs($operator)->get('/admin/dashboard');
            expect($response->status())->toBe(403,
                "Operator should be denied access to admin dashboard (iteration {$i})");
            
            // Verify Operator is denied access to Publisher routes
            $response = $this->actingAs($operator)->get('/publisher/dashboard');
            expect($response->status())->toBe(403,
                "Operator should be denied access to publisher dashboard (iteration {$i})");
            
            // Clean up for next iteration
            $operator->delete();
        }
    });

    /**
     * Property test: For any Publisher user, they should have access to Publisher routes
     * and be denied access to Admin and Operator specific routes.
     */
    test('publisher users can access publisher routes and are denied access to other role routes', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create a Publisher user with SKPD
            $skpd = Skpd::factory()->create();
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Verify Publisher can access Publisher dashboard
            $response = $this->actingAs($publisher)->get('/publisher/dashboard');
            expect($response->status())->toBe(200,
                "Publisher should have access to publisher dashboard (iteration {$i})");
            
            // Verify Publisher can access content management
            $response = $this->actingAs($publisher)->get('/publisher/content');
            expect($response->status())->toBe(200,
                "Publisher should have access to content management (iteration {$i})");
            
            // Verify Publisher is denied access to Admin routes
            $response = $this->actingAs($publisher)->get('/admin/dashboard');
            expect($response->status())->toBe(403,
                "Publisher should be denied access to admin dashboard (iteration {$i})");
            
            // Verify Publisher is denied access to Operator routes
            $response = $this->actingAs($publisher)->get('/operator/dashboard');
            expect($response->status())->toBe(403,
                "Publisher should be denied access to operator dashboard (iteration {$i})");
            
            // Clean up for next iteration
            $publisher->delete();
            $skpd->delete();
        }
    });

    /**
     * Property test: For any user role, the hasRole() method should correctly identify
     * the user's role.
     */
    test('hasRole method correctly identifies user roles', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create users of each role
            $admin = User::factory()->admin()->create();
            $operator = User::factory()->operator()->create();
            $skpd = Skpd::factory()->create();
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Verify Admin hasRole checks
            expect($admin->hasRole('Admin'))->toBeTrue(
                "Admin's hasRole('Admin') should return true (iteration {$i})");
            expect($admin->hasRole('Operator'))->toBeFalse(
                "Admin's hasRole('Operator') should return false (iteration {$i})");
            expect($admin->hasRole('Publisher'))->toBeFalse(
                "Admin's hasRole('Publisher') should return false (iteration {$i})");
            
            // Verify Operator hasRole checks
            expect($operator->hasRole('Admin'))->toBeFalse(
                "Operator's hasRole('Admin') should return false (iteration {$i})");
            expect($operator->hasRole('Operator'))->toBeTrue(
                "Operator's hasRole('Operator') should return true (iteration {$i})");
            expect($operator->hasRole('Publisher'))->toBeFalse(
                "Operator's hasRole('Publisher') should return false (iteration {$i})");
            
            // Verify Publisher hasRole checks
            expect($publisher->hasRole('Admin'))->toBeFalse(
                "Publisher's hasRole('Admin') should return false (iteration {$i})");
            expect($publisher->hasRole('Operator'))->toBeFalse(
                "Publisher's hasRole('Operator') should return false (iteration {$i})");
            expect($publisher->hasRole('Publisher'))->toBeTrue(
                "Publisher's hasRole('Publisher') should return true (iteration {$i})");
            
            // Clean up for next iteration
            $admin->delete();
            $operator->delete();
            $publisher->delete();
            $skpd->delete();
        }
    });

    /**
     * Property test: For any unauthenticated user, all protected routes should
     * redirect to login page.
     */
    test('unauthenticated users are redirected to login for protected routes', function () {
        // Define protected routes for each role
        $adminRoutes = ['/admin/dashboard', '/admin/skpd', '/admin/user', '/admin/kategori'];
        $operatorRoutes = ['/operator/dashboard', '/operator/verification', '/operator/monitoring'];
        $publisherRoutes = ['/publisher/dashboard', '/publisher/content'];
        
        $allProtectedRoutes = array_merge($adminRoutes, $operatorRoutes, $publisherRoutes);
        
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Pick a random protected route
            $route = $allProtectedRoutes[array_rand($allProtectedRoutes)];
            
            // Verify unauthenticated access redirects to login
            $response = $this->get($route);
            expect($response->status())->toBe(302,
                "Unauthenticated access to {$route} should redirect (iteration {$i})");
            
            $location = $response->headers->get('Location');
            $containsLogin = str_contains($location, 'login');
            expect($containsLogin)->toBeTrue(
                "Unauthenticated access to {$route} should redirect to login, got: {$location} (iteration {$i})");
        }
    });

    /**
     * Property test: For any role, middleware should return 403 for unauthorized access
     * regardless of the specific route within another role's domain.
     */
    test('middleware returns 403 for any unauthorized route access', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Create users of each role
            $admin = User::factory()->admin()->create();
            $operator = User::factory()->operator()->create();
            $skpd = Skpd::factory()->create();
            $publisher = User::factory()->publisher($skpd)->create();
            
            // Define routes for each role
            $adminRoutes = ['/admin/dashboard', '/admin/skpd'];
            $operatorRoutes = ['/operator/dashboard', '/operator/verification'];
            $publisherRoutes = ['/publisher/dashboard', '/publisher/content'];
            
            // Test Admin accessing random Operator route
            $operatorRoute = $operatorRoutes[array_rand($operatorRoutes)];
            $response = $this->actingAs($admin)->get($operatorRoute);
            expect($response->status())->toBe(403,
                "Admin accessing {$operatorRoute} should get 403 (iteration {$i})");
            
            // Test Admin accessing random Publisher route
            $publisherRoute = $publisherRoutes[array_rand($publisherRoutes)];
            $response = $this->actingAs($admin)->get($publisherRoute);
            expect($response->status())->toBe(403,
                "Admin accessing {$publisherRoute} should get 403 (iteration {$i})");
            
            // Test Operator accessing random Admin route
            $adminRoute = $adminRoutes[array_rand($adminRoutes)];
            $response = $this->actingAs($operator)->get($adminRoute);
            expect($response->status())->toBe(403,
                "Operator accessing {$adminRoute} should get 403 (iteration {$i})");
            
            // Test Operator accessing random Publisher route
            $publisherRoute = $publisherRoutes[array_rand($publisherRoutes)];
            $response = $this->actingAs($operator)->get($publisherRoute);
            expect($response->status())->toBe(403,
                "Operator accessing {$publisherRoute} should get 403 (iteration {$i})");
            
            // Test Publisher accessing random Admin route
            $adminRoute = $adminRoutes[array_rand($adminRoutes)];
            $response = $this->actingAs($publisher)->get($adminRoute);
            expect($response->status())->toBe(403,
                "Publisher accessing {$adminRoute} should get 403 (iteration {$i})");
            
            // Test Publisher accessing random Operator route
            $operatorRoute = $operatorRoutes[array_rand($operatorRoutes)];
            $response = $this->actingAs($publisher)->get($operatorRoute);
            expect($response->status())->toBe(403,
                "Publisher accessing {$operatorRoute} should get 403 (iteration {$i})");
            
            // Clean up for next iteration
            $admin->delete();
            $operator->delete();
            $publisher->delete();
            $skpd->delete();
        }
    });
});
