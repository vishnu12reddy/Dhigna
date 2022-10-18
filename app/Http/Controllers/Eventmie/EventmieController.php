<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\EventmieController as BaseEventmieController;
use Auth;

class EventmieController extends BaseEventmieController
{
    public function logout()
    {
        Auth::logout();
        //CUSTOM
        session()->flush();
        //CUSTOM
        $redirect = !empty(config('eventmie.route.prefix')) ? config('eventmie.route.prefix') : '/';
        return redirect($redirect);
    }
}
