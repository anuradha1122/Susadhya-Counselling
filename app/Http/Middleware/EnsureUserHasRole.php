<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        abort_unless($user, 401);

        abort_unless(
            $user->hasAnyRole($roles),
            403,
            'You are not authorized to access this section.'
        );

        return $next($request);
    }
}
