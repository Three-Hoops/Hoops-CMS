<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = array_map(fn(string $r) => UserRole::from($r), $roles);
        $user = $request->user();

        if (! $user instanceof User || ! in_array($user->role, $allowedRoles)) {
            abort(403);
        }

        return $next($request);
    }
}
