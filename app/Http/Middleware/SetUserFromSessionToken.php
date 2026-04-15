<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserFromSessionToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('api_token');

        if ($token) {
            $user = User::where('api_token', $token)->first();
            if ($user) {
                auth()->setUser($user);
                $request->setUserResolver(fn () => $user);
            }
        }

        return $next($request);
    }
}
