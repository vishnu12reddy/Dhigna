<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class SubOrganizer
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
        if(Auth::check())
        {
            //except porfile routes
            $current_route = \Request::route()->getName();
            
            if($current_route != 'eventmie.updateAuthUser' && $current_route != 'eventmie.updateAuthUserRole')
            {

                if(Auth::user()->hasRole('manager'))
                {
                    $user = Auth::user();

                    $user->id      = $user->organizer_id;
                    $user->role_id = 2;
                    $user->role->name = 'organiser';
                    $user->is_manager =  1;
                    Auth::setUser($user);

                    // dd(Auth::user());
                    
                }
            }
        }
    
        return $next($request);
    }
}
