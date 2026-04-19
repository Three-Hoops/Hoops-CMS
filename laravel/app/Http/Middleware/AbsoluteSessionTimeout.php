<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AbsoluteSessionTimeout
{
    public function handle(Request $request, Closure $next, int $hours = 8): Response
    {
        if ($request->session()->has('session_started_at')) {
            $startedAt = $request->session()->get('session_started_at');

            if ($startedAt->diffInHours(now()) >= $hours) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', 'Your session has expired. Please log in again.');
            }
        } else {
            $request->session()->put('session_started_at', now());
        }

        return $next($request);
    }
}
?>