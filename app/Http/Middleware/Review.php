<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
class Review
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!Auth::check())
            return redirect()->route('eventmie.welcome');
            
        if(Auth::user()->hasRole('admin') ||  Auth::user()->hasRole('organiser'))
            return $next($request);
        
        return redirect()->route('eventmie.welcome');

    }
}
