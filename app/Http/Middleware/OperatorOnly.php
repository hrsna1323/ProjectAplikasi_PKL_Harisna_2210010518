<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OperatorOnly
{
    /**
     * Handle an incoming request.
     *
     * Verifies user has Operator role.
     * Returns 403 if role doesn't match.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('Operator')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Operator access required.'
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini. Hanya Operator yang diizinkan.');
        }

        return $next($request);
    }
}
