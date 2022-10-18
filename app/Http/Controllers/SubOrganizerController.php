<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Http\Response;

class SubOrganizerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['only_organizer']);    
    }

    public  function index()
    {
        // get prifex from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');
        
        return view('sub_organizer.index', compact('path'));
    }

    /**
     *  get  sub-organizers for particular organiser
     */

    public function get_sub_organizers(Request $request)
    {
        // get guests for specific organizer
        $params = [
            'organizer_id'  => Auth::id(),   // only organizer_id
        ];
        
        $sub_organizers = User::select(['users.*', 'roles.name as role_name'])->leftJoin('roles', 'roles.id', '=', 'users.role_id')->where($params)->paginate(10);

        if($sub_organizers->isEmpty())
            return error(__('eventmie-pro::em.guest').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        
        return response([
            'sub_organizers'=> $sub_organizers->jsonSerialize(),
        ], Response::HTTP_OK);

    }

    /**
     *   edit_sub_organizer
     */

    public function edit_sub_organizer(Request $request)
    {
        $request->validate([
            'sub_organizer_id' => 'required|numeric|gt:0',
            'name'             => 'required',
            'email'            => 'required|email'
        ]);

        $user = User::where(['id' => $request->sub_organizer_id])->first();
        
        if(empty($user))
        {
            return response()->json(['status' => false]);
        }
        
        if($user->email != $request->email)
            $request->validate([
                'email' => 'unique:users,email'
            ]);


        $params = [
            'name'   => $request->name,
            'email'  => $request->email,
        ];

        User::where(['id' =>  $request->sub_organizer_id])->update($params);

        return response()->json(['status' => true]);
    }

    

}
