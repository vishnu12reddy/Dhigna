<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Classiebit\Eventmie\Models\Event;
use Redirect;

class PrivateEventController extends Controller
{

        /**
     * Create a new controller instance.
     * 
     *
     * @return void
     */
    public function __construct()
    {
        // language change
        $this->middleware('common');
        
        $this->middleware('organiser')->except('verify_event_password');
        
        $this->event    = new Event;

    }

    /**
     *  save event password for private event
     */
    public function save_password(Request $request)
    {
        $request->validate([
            'event_id'                 => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'event_password'           => 'string|max:512|nullable',
            'is_private'               => 'nullable'
        ]);
 
        $params = [
            'event_password' =>  $request->event_password,
            'is_private'      => !empty($request->is_private) ? 1 : 0,
        ];

        if(!empty($request->event_password))
        {
            $params['is_private'] = 1;
        }

        $this->event->save_event($params, $request->event_id);

        return response()->json(['status' => true]);
    }

    /**
     *  verifiy event password and set password in user session
     */

    public function verify_event_password(Request $request)
    {
        $request->validate([
            'event_id'                 => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'event_password'           => 'required|max:512',
        ]);

        $event = Event::where(['id' => $request->event_id])->first();
        if($event)
        {
            // check password and password is same then set password in session
            if(\Str::is($request->event_password, $event->event_password))
            {
                // set event password in auth session
                \Session::put('event_password_'.$event->id, $event->event_password);
                
                return redirect()->route('eventmie.events_show', [$event->slug]);
            }
        }



        // if don't match password then will show error
        $msg = __('eventmie-pro::em.invalid').' '.__('eventmie-pro::em.password');
        session()->flash('error', $msg);
        
        return redirect()->back()->withErrors([ 'event_password' =>  $msg]);

    
    }
}
