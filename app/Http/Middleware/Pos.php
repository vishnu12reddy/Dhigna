<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Pos
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
        if(Auth::check())
            if(Auth::user()->hasRole('pos') || Auth::user()->hasRole('organiser') || Auth::user()->hasRole('admin') || Auth::user()->hasRole('scanner'))
                return $next($request);
                
        if($request->ajax());
        {
            return response()->json(['status' => false, 'message' => __('eventmie-pro::em.unauthorized')]);
        }

        return redirect()->route('eventmie.welcome');
    }
}
