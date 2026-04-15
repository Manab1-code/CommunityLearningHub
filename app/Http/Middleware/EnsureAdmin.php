<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect('/auth/signin')->with('error', 'Please sign in.');
        }

        if (! auth()->user()->isAdmin()) {
            return redirect('/home')->with('error', 'Access denied. Admin only.');
        }

        return $next($request);
    }
}
