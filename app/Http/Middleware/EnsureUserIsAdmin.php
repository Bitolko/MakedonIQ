<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Admin access requires authentication.'], 403);
            }

            return redirect()->guest(route('login'));
        }

        if (! $user->is_admin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Admin access required.'], 403);
            }

            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
