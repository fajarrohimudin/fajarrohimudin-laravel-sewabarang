<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle($request, Closure $next)
    {
        // dd('middleware jalan');
        if (! Auth::check() || Auth::user()->roles !== 'ADMIN') {
            return redirect()->route('front.index');
        }

        return $next($request);
    }
}
