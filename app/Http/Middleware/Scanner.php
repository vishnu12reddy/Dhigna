<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Scanner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Allow all except guest & customer
        if(!Auth::check())
            return redirect()->route('eventmie.welcome');
        
        if(Auth::user()->hasRole('customer'))
            return redirect()->route('eventmie.welcome');

        return $next($request);
    }
}
