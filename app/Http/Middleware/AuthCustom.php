<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthCustom
{
    /**
     * Session timeout in minutes.
     * This should match SESSION_LIFETIME in .env
     */
    protected int $sessionTimeout = 120;

    /**
     * Handle an incoming request.
     *
     * Verifies user is logged in and session is valid.
     * Redirects to login if not authenticated or session expired.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Check if this was a session timeout
            $wasLoggedIn = $request->session()->has('was_logged_in');
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $message = $wasLoggedIn 
                ? 'Sesi Anda telah berakhir. Silakan login kembali.'
                : 'Silakan login terlebih dahulu.';

            return redirect()->route('login')
                ->with('error', $message);
        }

        // Check if user is active
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is inactive.'], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Check for session timeout based on last activity
        $lastActivity = $request->session()->get('last_activity');
        $now = time();
        
        if ($lastActivity && ($now - $lastActivity) > ($this->sessionTimeout * 60)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('was_logged_in', true);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired.'], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
        }

        // Update last activity timestamp
        $request->session()->put('last_activity', $now);
        
        // Mark that user was logged in (for timeout detection)
        $request->session()->put('was_logged_in', true);

        return $next($request);
    }
}
