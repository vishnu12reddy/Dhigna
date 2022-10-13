<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Glist;
use Auth;
use Classiebit\Eventmie\Models\Booking;
use App\Models\User;
use App\Models\Guest;
use App\Mail\SendMailGuests;
use Illuminate\Support\Facades\Mail;


class GlistsController extends Controller
{
    public $organiser_id;

    public function __construct()
    {
        $this->middleware('common');

        $this->middleware('organiser');

        $this->glist = new Glist();

        \Config::set('mail.driver', setting('mail.mail_driver'));
        \Config::set('mail.host', setting('mail.mail_host'));
        \Config::set('mail.port', setting('mail.mail_port'));
        \Config::set('mail.username', setting('mail.mail_username'));
        \Config::set('mail.password', setting('mail.mail_password'));
        \Config::set('mail.encryption', setting('mail.mail_encryption'));
        \Config::set('mail.from', ['address' => setting('mail.mail_sender_email'), 'name' => setting('mail.mail_sender_name')]);
    }

    /**
     *   load view
     */

    public  function index()
    {
        // get prifex from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');
        
        return view('guests.guestlist', compact('path'));
    }

    /**
     *  add my create_glist
     */

    public function create_glist(Request $request)
    {
        
        $request->validate([
            'name'           => 'required|max:512',
        ]);
 
        $params = [
            'name'           => $request->name,
            'user_id'        => Auth::id()   // only organizer_id
        ];

        //glist id in edit case
        $glist_id  = (int)$request->glist_id;

        // save guestlist
        $glist = $this->glist->create_glist($params, $glist_id);
        
        if(empty($glist))
            return response()->json(['status' => false, 'msg' => __('eventmie-pro::em.guestlist').' '.__('eventmie-pro::em.could_not').' '.__('eventmie-pro::em.created')]);

        return response()->json(['status' => true]);
        
    }

    /**
     *  get my glist 
     *  for dropdown myglists where orgnizer add guest and choose glist from dropdwon
     */

    public function get_myglist(Request $request)
    {
        $this->is_admin($request);

        $params = [
            'user_id'  => $this->organiser_id   // only organizer_id
        ];
        // get guestlist
        $myglist = $this->glist->get_glist($params);
        
        if($myglist->isEmpty())
            return response()->json(['status' => true, 'myglist' => [], 'msg' => __('eventmie-pro::em.guestlist').' '.__('eventmie-pro::em.not_found')]);

        return response()->json(['status' => true, 'myglist' => $myglist ]);
        
    }

    /**
     *  get my glist 
     *  
     * for pagination gilist where show all glist
     */

    public function pagination_myglists()
    {
        $params = [
            'user_id'  => Auth::id()   // only organizer_id
        ];
        // get guestlist
        $myglists = $this->glist->pagination_glists($params);
        
        
        if($myglists->isEmpty())
            return error(__('eventmie-pro::em.guestlist').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        
        return response([
            'myglists'=> $myglists->jsonSerialize(),
        ], Response::HTTP_OK);
        
    }

    /**
     *  add to glist
     */

    public function add_to_glist(Request $request)
    {
        $this->is_admin($request);

        $request->validate([
            'event_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
        ]);

        // convert into array
        $glist_ids  = json_decode($request->glist_ids, true);
        
        if(empty($glist_ids))
        {
            $request->validate([
                'glist_ids'       => 'required|array|min:1',
                
            ]);
        }

        $event_id    = (int)$request->event_id;
    
        // get customers of particular event from booking table
        $customer_ids = Booking::where(['event_id' => $event_id])->get()->pluck('customer_id')->all();
        
        if(empty($customer_ids))
            return response()->json(['status' => false,'msg' => __('eventmie-pro::em.customers').' '.__('eventmie-pro::em.not_found')]);

        // get customer 
        $customers  = User::whereIn('id', $customer_ids)->get();
        
        // create guest
        $guests =   $customers->map(function ($customer, $key) use($glist_ids) {
                        
                        $params = [
                            'user_id' => $this->organiser_id,
                            'name'    => $customer['name'],
                        ];

                        // save guest
                        // if have no guest email then create new event
                        $guest = Guest::firstOrCreate(
                                    ['email' => $customer['email']],
                                    $params
                                );
                        
                        // save data in pivot table
                        Guest::find($guest->id)->glists()->sync($glist_ids);

                        return $guest;

                    });

        return response()->json(['status' => true, 'guests' => $guests ]);

    }

    /**
     *  send email to all guest of particular guest list
     */

    public function export_emails(Glist $glist)
    {
        
        // check guests of glist empty or not 
        if($glist->guests->isEmpty())
            return redirect()->back()->withErrors(['status' => false,'msg' => __('eventmie-pro::em.guests').' '.__('eventmie-pro::em.not_found')]);
            
        
        // create object of laracsv
        $csvExporter = new \Laracsv\Export();
    
        // create csv 
        $csvExporter->build($glist->guests, [
            
            //events fields which will be include
            'email',
            
        ]);
        
        // download csv
        $csvExporter->download($glist->name.'-guests.csv');

    }

    /**
     *  delete glist
     */

    public function delete_glist(Request $request)
    {
        $request->validate([
            'glist_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
        ]);

        $glist_id     = (int)$request->glist_id;

        $params = [
            'user_id'  => Auth::id(),   // organizer_id
            'id'       => $glist_id
        ];

        $this->glist->delete_glist($params);

        return response()->json(['status' => true ]);
    }

    /**
     *  send bluk email
     */

    public function send_bluk_email(Request $request)
    {
        $request->validate([
            'event_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'message'           => 'required|max:1000'
        ]);
        
        $event_id    = (int)$request->event_id;
    
        // get customers of particular event from booking table
        $customer_ids = Booking::where(['event_id' => $event_id])->get()->pluck('customer_id')->all();
        
        if(empty($customer_ids))
            return response()->json(['status' => false,'msg' => __('eventmie-pro::em.customers').' '.__('eventmie-pro::em.not_found')]);

        // get customer 
        $customers  = User::whereIn('id', $customer_ids)->get();
        
        // customers emails
        $customers_emails = $customers->map(function ($customer, $key)  {
                                return $customer['email'];
                            })->all();

        $admin_email      = User::where(['id' => 1])->first()->email;           // admin;                     
        
        // mail body    
        $mail     = [
            'body' => $request->message,
        ]; 

        // send mails
        Mail::to($admin_email)
        ->bcc($customers_emails)
        ->send(new SendMailGuests($mail));

        return response()->json(['status' => true ]);
    }

    /**
     *  send bluk email to all users
     */

    public function send_bluk_email_to_all(Request $request)
    {
        $request->validate([
            'message'           => 'required|max:1000'
        ]);

        // get all users except admin 
        $users_email  = User::where('id', '>', 1)->get()->pluck('email')->all();

        if(empty($users_email))
            return response()->json(['status' => false,'msg' => __('eventmie-pro::em.users').' '.__('eventmie-pro::em.not_found')]);

        $admin_email      = User::where(['id' => 1])->first()->email;           // admin;                     
        
        // mail body    
        $mail     = [
            'body' => $request->message,
        ]; 

        // send mails
        Mail::to($admin_email)
        ->bcc($users_email)
        ->send(new SendMailGuests($mail));

        return response()->json(['status' => true ]);
    }

    // check login user role
    protected function is_admin(Request $request)
    {
        // if login user is Organiser then 
        // organiser id = Auth::id();
        $this->organiser_id = Auth::id();

        // if admin is creating event
        // then user Auth::id() as $organiser_id
        // and organiser id will be the id selected from Vue dropdown
        if(Auth::user()->hasRole('admin'))
        {
            $request->validate([
                'organiser_id'       => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            ]);
            $this->organiser_id = $request->organiser_id;
        }

    }
}
