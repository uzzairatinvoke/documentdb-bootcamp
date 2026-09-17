<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware semakan peranan (role) sebelum request sampai ke controller.
 * Contoh route: ->middleware('role:admin') atau ->middleware('role:admin,manager')
 */
class CheckRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tiada kebenaran untuk akses ini.');
        }

        return $next($request);
    }
}
