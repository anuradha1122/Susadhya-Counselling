<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless($user, 401);
        abort_unless($user->hasAnyPermission($permissions), 403, 'You are not authorized to access this section.');

        return $next($request);
    }
}
