<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperadmin
{
    /**
     * Allow the request through only for superadmin users; everyone else
     * (including guests) gets a 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_superadmin) {
            abort(403);
        }

        return $next($request);
    }
}
