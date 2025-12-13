<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ])->onlyInput('email');
            }

            // Log the login activity
            $this->activityLogService->logUserAction(
                'user_login',
                'User logged in successfully',
                $user
            );

            // Redirect based on role
            return $this->redirectBasedOnRole($user->role);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            // Log the logout activity
            $this->activityLogService->logUserAction(
                'user_logout',
                'User logged out',
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Get the current user's role.
     */
    public function checkRole(): string
    {
        $user = Auth::user();

        if (!$user) {
            return 'guest';
        }

        return $user->role;
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        return match ($role) {
            'Admin' => redirect()->route('admin.dashboard')->with('success', 'Selamat datang, Admin!'),
            'Operator' => redirect()->route('operator.dashboard')->with('success', 'Selamat datang, Operator!'),
            'Publisher' => redirect()->route('publisher.dashboard')->with('success', 'Selamat datang, Publisher!'),
            default => redirect()->route('dashboard')->with('success', 'Selamat datang!'),
        };
    }
}
