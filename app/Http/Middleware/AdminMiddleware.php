<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401); // 401 Unauthorized (brak logowania)
        }

        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Admins only.',
            ], 403); // 403 Forbidden (brak uprawnień)
        }

        return $next($request);
    }
}
