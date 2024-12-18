<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
         if (Auth::check() && Auth::user()->role !== 1) {
            Auth::logout(); // Log out the user
            return redirect('/')->with('error', 'You do not have access to this page. Please login on app');
        }
 
        return $next($request);
    }
}
