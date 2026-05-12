<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Update last_login_at sekali per sesi (tidak tiap request)
            if (!$request->session()->get('last_login_recorded')) {
                $user->timestamps = false;
                $user->update(['last_login_at' => now()]);
                $user->timestamps = true;

                $request->session()->put('last_login_recorded', true);
            }
        }

        return $next($request);
    }
}
