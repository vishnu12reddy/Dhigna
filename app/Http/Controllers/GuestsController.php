<?php

namespace App\Http\Controllers;

use App\Models\Glist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Guest;
use Auth;

class GuestsController extends Controller
{
    public function __construct()
    {
        $this->middleware('common');

        $this->middleware('only_organizer');

        $this->guest = new Guest();
    }

    /**
     *  
     */

    public  function index(Glist $glist)
    {
        // get prifex from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');
        
        $glist_id  = (int)$glist->id;
        
        return view('guests.guestlist', compact('path', 'glist_id'));
    }

    /**
     *  get myguest for particular organiser
     */

    public function get_myguests(Request $request)
    {
        $glist_id = (int)$request->glist_id;
        
        $glist = Glist::where(['id' => $glist_id ])->first();
        
        if(empty($glist))
            return error(__('eventmie-pro::em.guest').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );

        // get guests for specific organizer
        $params = [
            'user_id'  => Auth::id(),   // only organizer_id
            'id'       => $glist->id,
        ];
        
        $guests = $this->guest->get_guests($params);
        
        if($guests->isEmpty())
            return error(__('eventmie-pro::em.guest').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        
        return response([
            'myguests'=> $guests->jsonSerialize(),
        ], Response::HTTP_OK);

    }

        /**
     *  add my add_guest
     */

    public function add_guest(Request $request)
    {
        $request->validate([
            'name'            => 'max:512|nullable',
            'email'           => 'required|email|unique:guests,email',
        ]);

        // convert into array
        $glist_ids  = json_decode($request->glist_ids, true);
        
        if(empty($glist_ids))
        {
            $request->validate([
                'glist_ids'       => 'required|array|min:1',
                
            ]);
        }
        
        // guest id in edit case
        $guest_id            = (int) $request->guest_id;

        $params = [
            'name'           => $request->name,
            'user_id'        => Auth::id(),
            'email'          => $request->email,
        ];

        // save guest
        $guest = $this->guest->add_guest($params, $guest_id);
       
        if(empty($guest))
            return response()->json(['status' => false, 'msg' => __('eventmie-pro::em.guest').' '.__('eventmie-pro::em.could_not').' '.__('eventmie-pro::em.created')]);
        
        // save data in pivot table
        Guest::find($guest->id)->glists()->sync($glist_ids);

        return response()->json(['status' => true]);

        
    }

    /**
     *  delete guest means remove from glist 
     */

    public  function delete_guest(Request $request)
    {
        $request->validate([
            'guest_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
        ]);

        $guest_id     = (int)$request->guest_id;

        
        $guest = Guest::with('glists')->where(['id' => $guest_id])->first();

        if(empty($guest))
            return response()->json(['status' => false, 'msg' => __('eventmie-pro::em.guest').' '.__('eventmie-pro::em.not_found')]);

        if($guest->glists->isEmpty())  
            return response()->json(['status' => false, 'msg' => __('eventmie-pro::em.guestlist').' '.__('eventmie-pro::em.not_found')]);
            
        // remove from glist the guest
        $guest->glists()->sync([]);
        
        return response()->json(['status' => true, 'msg' => __('eventmie-pro::em.deleted').' '.__('eventmie-pro::em.successfully')]);

    }

    /**
     *  delete guest means remove from glist 
     */

    public  function edit_guest(Request $request)
    {
        $request->validate([
            'guest'           => 'gt:0|numeric',     
            'name'            => 'max:512|nullable',
            'email'           => 'required|email|unique:guests,email,'.$request->guest_id,
        ]);

        $guest = Guest::where(['id' => $request->guest_id])->first(); 

        if(empty($guest))
            return response()->json(['status' => false, 'msg' => __('eventmie-pro::em.guest').' '.__('eventmie-pro::em.could_not').' '.__('eventmie-pro::em.found')]);
        
        $guest->name = $request->name;
        $guest->email = $request->email;

        $guest->save();
            
        return response()->json(['status' => true, 'msg' => __('eventmie-pro::em.edit').' '.__('eventmie-pro::em.successfully')]);

    }

    
}
