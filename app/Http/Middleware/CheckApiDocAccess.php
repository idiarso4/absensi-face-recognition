<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckApiDocAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized: You must be logged in to access this resource.');
        }

        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        abort(403, 'Forbidden: You do not have permission to access the API documentation.');
    }
}
