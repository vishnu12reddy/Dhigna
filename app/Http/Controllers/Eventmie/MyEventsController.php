<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\MyEventsController as BaseMyEventsController;
use App\Models\Event;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Booking;
use Facades\Classiebit\Eventmie\Eventmie;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Classiebit\Eventmie\Models\Venue;

class MyEventsController extends BaseMyEventsController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // CUSTOM
        $this->event      = new Event;
        $this->booking    = new Booking;
        // CUSTOM
    
    }

    // only organiser can see own events and admin or customer can't see organiser events 

    public function index($view = 'vendor.eventmie-pro.myevents.index', $extra = [])
    {
        // get prifex from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');
        // admin can't see organiser bookings
        // if(Auth::user()->hasRole('admin'))
        // {
        //     return redirect()->route('voyager.events.index');   
        // }
        
        //CUSTOM
        $is_admin   = Auth::user()->hasRole('admin') ? 1 : 0;
        //CUSTOM
     
        // organizer 
        $organizer_id = Auth::id();

        return view($view, compact('path', 'organizer_id', 'extra', 'is_admin'));

        //CUSTOM    
        
    }

    /**
     * Create-edit event
     *
     * @return array
     */
    public function form($slug = null, $view = 'vendor.eventmie-pro.events.form', $extra = [])
    {
        $event  = [];
        
        // get event by event_slug
        if($slug)
        {
            $event  = $this->event->get_event($slug);
            $event  = $event->makeVisible('online_location');

            /* CUSTOM */
            $event  = $event->makeVisible('youtube_embed');
            $event  = $event->makeVisible('vimeo_embed');
            /* CUSTOM */
            
            // user can't edit other user event but only admin can edit event's other users
            if(!Auth::user()->hasRole('admin') && Auth::id() != $event->user_id)
                return redirect()->route('eventmie.events_index');
        }
    
        $organisers = [];
        // fetch organisers dropdown
        // only if login user is admin
        if(Auth::user()->hasRole('admin'))
        {
            // fetch organisers
            $organisers    = $this->event->get_organizers(null);
            foreach($organisers as $key => $val)
                $organisers[$key]->name = $val->name.'  ( '.$val->email.' )';

            if($slug)
            {
                // in case of edit event, organiser_id won't change
                $this->organiser_id = $event->user_id;    
            }
        }
        
        $organiser_id             = $this->organiser_id ? $this->organiser_id : 0;
        $selected_organiser       = User::find($this->organiser_id);
        
        return Eventmie::view($view, compact('event', 'organisers', 'organiser_id', 'extra', 'selected_organiser'));
    }

    // create event
    public function store(Request $request)
    {
        // if logged in user is admin
        $this->is_admin($request);
        
        // 1. validate data
        $request->validate([
            'title'             => 'required|max:256',
            'excerpt'           => 'required|max:512',
            'slug'              => 'required|max:512',
            'category_id'       => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'description'       => 'required',
            'faq'               => 'nullable',
            'offline_payment_info' => 'nullable|max:2048',
            // CUSTOM
            'short_url'         => 'nullable|max:256',
            'currency'          => 'nullable',
            'show_reviews'      => 'nullable'
            // CUSTOM
        ], [
            'category_id.*' => __('eventmie-pro::em.category').' '.__('eventmie-pro::em.required')
        ]);

        
        $result             = (object) [];
        $result->title      = null;
        $result->excerpt    = null;
        $result->slug       = null;
        //CUSTOM
        $result->short_url  = null;
        //CUSTOM
        
        // in case of edit
        if(!empty($request->event_id))
        {
            $request->validate([
                'event_id'       => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            ]);

            // check this event id have login user relationship
            $result      = (object) $this->event->get_user_event($request->event_id, $this->organiser_id);
        
            if(empty($result))
                return error('access denied', Response::HTTP_BAD_REQUEST );
    
        }
        
        // title is not equal to before title then apply unique column rule    
        if($result->title != $request->title)
        {
            $request->validate([
                'title'             => 'unique:events,title',
            ]);
        }
        
        // slug is not equal to before slug then apply unique column rule    
        if($result->slug != $request->slug)
        {
            $request->validate([
                'slug'             => 'unique:events,slug',
            ]);
        }
          //CUSTOM
        // short_url is not equal to before short_url then apply unique column rule    
        if($result->short_url != $request->short_url && !empty($request->short_url))
        {
            $request->validate([
                'short_url'             => 'unique:events,short_url',
            ]);
        }
        //CUSTOM

        $params = [
            "title"         => $request->title,
            "excerpt"       => $request->excerpt,
            "slug"          => $this->slugify($request->slug),
            "description"   => $request->description,
            "faq"           => $request->faq,
            "category_id"   => $request->category_id,
            "featured"      => 0,
            "offline_payment_info" => $request->offline_payment_info,
            //CUSTOM
            "short_url"     => $request->short_url,
            "currency"      => $request->currency,
            "e_soldout"     => !empty($request->e_soldout) ? 1 : 0,
            "show_reviews"  => !empty($request->show_reviews) ? 1 : 0,
            //CUSTOM
        ];

        //CUSTOM
        if(Auth::user()->hasRole('admin'))
               $params = $this->e_admin_commission($request, $params);
        //CUSTOM

        
        //featured
        if(!empty($request->featured))
        {
            $request->validate([
                'featured'       => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            ]);

            $params["featured"]       = $request->featured;
        }

        // Admin controls status via checkbox
        if(Auth::user()->hasRole('admin'))
        {
            $status             = (int) $request->status;
            $params["status"]   = $status ? 1 : 0;
        }
        else
        {
            // organizer event status will be controlled by admin
            // - when organizer login
            // - when creating event
            if(empty($request->event_id))
            {
                // - manual approval on
                if(setting('multi-vendor.verify_publish'))
                {
                    $params["status"] = 0;
                }
                else
                {
                    // - manual approval off
                    $params["status"] = 1;
                }
            }
        }
        
        // only at the time of event create
        if(!$request->event_id)
        {
            $params["user_id"]       = $this->organiser_id;
            $params["item_sku"]      = (string) time().rand(1,98);
        }
        
        $event    = $this->event->save_event($params, $request->event_id);
        
        if(empty($event))
            return error(__('eventmie-pro::em.event_not_created'), Response::HTTP_BAD_REQUEST );

        // ====================== Notification ====================== 
        //send notification after bookings
        $msg[]                  = __('eventmie-pro::em.event').' - '.$event->title;
        $extra_lines            = $msg;

        $mail['mail_subject']   = __('eventmie-pro::em.event_created');
        $mail['mail_message']   = __('eventmie-pro::em.event_info');
        $mail['action_title']   = __('eventmie-pro::em.manage_events');
        $mail['action_url']     = route('eventmie.myevents_index');
        $mail['n_type']         = "events";

        /* CUSTOM */
        $mail['event']          = $event;
        /* CUSTOM */

        $notification_ids       = [1, $this->organiser_id];
        
        $users = User::whereIn('id', $notification_ids)->get();
        try {
            // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail, $extra_lines));

            //CUSTOM
            \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'event')->delay(now()->addSeconds(10));
            // test
            // return view('email_templates.event', compact('mail'));
            //CUSTOM
        } catch (\Throwable $th) {}
        // ====================== Notification ======================     
        
        
        // in case of create
        if(empty($request->event_id))
        {
            // set step complete
            $this->complete_step($event->is_publishable, 'detail', $event->id);
            return response()->json(['status' => true, 'id' => $event->id, 'organiser_id' => $event->user_id , 'slug' => $event->slug ]);
        }    
        // update event in case of edit
        $event      = $this->event->get_user_event($request->event_id, $this->organiser_id);
        return response()->json(['status' => true, 'slug' => $event->slug]);    
    }

    // crate location of event
    public function store_location(Request $request)
    {
        // if logged in user is admin
        $this->is_admin($request);

        // 1. validate data
        $request->validate([
            'event_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'country_id'        => 'numeric|min:0',
            // 'venue'             => 'required|max:256',
            'address'           => 'max:512',
            'city'              => 'max:256',
            'state'             => 'max:256',
            'zipcode'           => 'max:64',
            'latitude'          => ['nullable','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude'         => ['nullable','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'online_location'   => 'nullable|string',
            
        ]);

        
        if(empty($request->online_event))
        {
            $request->validate([
                'venues_ids'        => 'required'
            ]);
        }

        $venue = Venue::whereId($request->venues_ids)->first();

        $params = [
            "country_id"    => !empty($venue) ? $venue->country_id : 0,
            "venue"         => !empty($venue) ? $venue->title : null,
            "address"       => !empty($venue) ? $venue->address : null,
            "city"          => !empty($venue) ? $venue->city : null,
            "zipcode"       => !empty($venue) ? $venue->zipcode : null,
            "state"         => !empty($venue) ? $venue->state : null,
            "latitude"      => !empty($venue) ? $venue->glat : null,
            "longitude"     => !empty($venue) ? $venue->glong : null,
            "online_location" => $request->online_location,

            //CUSTOM
            'youtube_embed' => $request->youtube_embed,
            'vimeo_embed'   => $request->vimeo_embed,
            //CUSTOM  
        ];

        // only at the time of event create
        if(!$request->event_id)
        {
            $params["user_id"]       = $this->organiser_id;
        }

        // check this event id have login user or not
        $check_event    = $this->event->get_user_event($request->event_id, $this->organiser_id);
        if(empty($check_event))
        {
            return error('access denied', Response::HTTP_BAD_REQUEST );
        }

        $location   = $this->event->save_event($params, $request->event_id);
        if(empty($location))
        {
            return error('Database failure!', Response::HTTP_BAD_REQUEST );
        }

        // get update event
        $event    = $this->event->get_user_event($request->event_id, $this->organiser_id);

        $venues_ids = [];
        if(!empty($request->venues_ids))
            $venues_ids = explode(",",$request->venues_ids);

        $event->venues()->sync($venues_ids);
        
        // set step complete
        $this->complete_step($event->is_publishable, 'location', $request->event_id);
        
        return response()->json(['status' => true, 'event' => $event]);
    }  

    // crate media of event
    public function store_media(Request $request)
    {
        // if logged in user is admin
        $this->is_admin($request);

        $images    = [];
        $poster    = null;
        $thumbnail = null;

        // 1. validate data
        $request->validate([
            'event_id'      => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'thumbnail'     => 'required',
            'poster'        => 'required',
        ]);

        // vedio link optional so if have vedio ling then validation apply
        if(!empty($request->video_link) && (!empty($request->video_link[0])))
        {
            $request->validate([
                //CUSTOM
                'video_link'      => 'required|array',
                'video_link.*'    => 'required',
                //CUSTOM
            ]);
        }
        
        $result              = [];
        // check this event id have login user or not
        $result    = $this->event->get_user_event($request->event_id, $this->organiser_id);

        if(empty($result))
        {
            return error('access denied', Response::HTTP_BAD_REQUEST );
        }
    
        // for multiple image
        $path = 'events/'.Carbon::now()->format('FY').'/';

        // for thumbnail
        if(!empty($_REQUEST['thumbnail'])) 
        { 
            $params  = [
                'image'  => $_REQUEST['thumbnail'],
                'path'   => 'events',
                'width'  => 512,
                'height' => 512,  
            ];
            $thumbnail   = $this->upload_base64_image($params);
        }

        if(!empty($_REQUEST['poster'])) 
        {
            $params  = [
                'image'  => $_REQUEST['poster'],
                'path'   => 'events',
                'width'  => 1920,
                'height' => 1080,  
            ];

            $poster   = $this->upload_base64_image($params);
        }
    
        // for image
        if($request->hasfile('images')) 
        { 
            // if have  image and database have images no images this event then apply this rule 
            $request->validate([
                'images'        => 'required',
                'images.*'      => 'mimes:jpeg,png,jpg,gif,svg',
            ]); 
        
            $files = $request->file('images');
    
            foreach($files as  $key => $file)
            {
                $extension       = $file->getClientOriginalExtension(); // getting image extension
                $image[$key]     = time().rand(1,988).'.'.$extension;
                $file->storeAs('public/'.$path, $image[$key]);
                
                $images[$key]    = $path.$image[$key];
            }
        }

         //CUSTOM
        // for seatingchart_image
        if($request->hasfile('seatingchart_image')) 
        { 
            // if have  image and database have images no images this event then apply this rule 
            $request->validate([
                'seatingchart_image'      => 'required|mimes:jpeg,png,jpg,gif,svg',
            ]); 
        
            $file = $request->file('seatingchart_image');
    
            
            $extension              = $file->getClientOriginalExtension(); // getting image extension
            $seatingchart_image     = time().rand(1,988).'.'.$extension;
            $file->storeAs('public/'.$path, $seatingchart_image);
            
            $seatingchart_image     = $path.$seatingchart_image;

        }
        //CUSTOM
        
        //CUSTOM
        $video_link         = null;
        if(!empty($request->video_link)) {
            if(is_array($request->video_link)) {
                if(!empty($request->video_link[0])) {
                    $video_link         = json_encode($request->video_link);
                }
            }
        }
        $params = [
            "thumbnail"     => !empty($thumbnail) ? $path.$thumbnail : null ,
            "poster"        => !empty($poster) ? $path.$poster : null,
            "video_link"    => $video_link,
            "user_id"       => $this->organiser_id,
        ];  

        // if images uploaded
        if(!empty($images))
        {
            if(!empty($result->images))
            {
                $exiting_images = json_decode($result->images, true);

                $images = array_merge($images, $exiting_images);
            }

            $params["images"] = json_encode(array_values($images));

        }    

        //CUSTOM
        // if seatingchart_image 
        if(!empty($seatingchart_image))
            $params["seatingchart_image"] = $seatingchart_image;    
            
        //CUSTOM 
        
        $status   = $this->event->save_event($params, $request->event_id);

        if(empty($status))
        {
            return error('Database failure!', Response::HTTP_BAD_REQUEST );
        }

        // get media  related event_id who have created now
        $images   = $this->event->get_user_event($request->event_id, $this->organiser_id);

        // set step complete
        $this->complete_step($images->is_publishable, 'media', $request->event_id);

        return response()->json(['images' => $images, 'status' => true]);
    }

    
    /**
     *  my  event for particular organiser
     */

    public function get_myevents(Request $request)
    {
        // if(Auth::user()->hasRole('admin'))
        // {
        //     return redirect()->route('voyager.events.index');   
        // }

        //CUSTOM
        $this->is_admin($request);
        //CUSTOM

        $params   = [
            // 'organiser_id' => Auth::id(),
            //CUSTOM
            'organiser_id' => $this->organiser_id,
            'search'            => $request->search,
            'length'            => $request->length,
            
            //CUSTOM
        ];

        $myevents    = $this->event->get_my_events($params);

        $myevents->makeVisible(['event_password']);

        if(empty($myevents))
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        
        //CUSTOM
        // add sub organizers    
        $myevents = $this->get_sub_organizers($myevents);
        //CUSTOM    
        
        
        return response([
            'myevents'=> $myevents->jsonSerialize(),
        ], Response::HTTP_OK);

    }

    /**
     *  get organiser with pagination
     */
    public function get_organizers(Request $request)
    {
        //CUSTOM
        if(!Auth::user()->hasRole('admin'))
        {
            $request->validate([
                'search'        => 'required|string|max:256',
            ]);
        }
        //CUSTOM

        $search     = $request->search;
        $organizers = $this->event->get_organizers($search);

        if(empty($organizers))
        {
            return response()->json(['status' => false, 'organizers' => []]);    
        }

        foreach($organizers as $key => $val)
            $organizers[$key]->name = $val->name.'  ( '.$val->email.' )';
        
        return response()->json(['status' => true, 'organizers' => $organizers ]);
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
    
    /**
     *  event admin commission
     */
    protected function e_admin_commission(Request $request, $params = [])
    {
        // 1. validate data
        $request->validate([
            'e_admin_commission' => 'nullable|numeric',
            
        ]);
        
        $params['e_admin_commission'] = $request->e_admin_commission;

        return $params;
    }

    /**
     *  set event currecny
     */

    protected function setEventCurrency(Request $request)
    {
        $this->currency = setting('regional.currency_default'); 
        
        // get event by event_id
        $event          = $this->event->get_event(null, $request->event_id);
        
        // if event not found then access denied
        if(empty($event))
            return ['status' => false, 'error' =>  __('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found')];
        
        if(!empty($event->currency))
            $this->currency = $event->currency;
            
    }


    /**
     *  save sub organizers
     */

    public function save_sub_organizers(Request $request)
    {
        // 1. validate data
        $request->validate([
            'event_id'                => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            
        ]);
        
        $event_id  = (int) $request->event_id;  
        
        // convet json data into array
        $pos       =  json_decode($request->pos_ids, true);
        $scanner   =  json_decode($request->scanner_ids, true);
        
        // for pos sub organizers
        $this->prepare_sub_organizers($pos, $event_id, 4);        
    
        // for scanner sub organizers
        $this->prepare_sub_organizers($scanner, $event_id, 5);  
        
        // redirect 
        $url = route('eventmie.myevents_index');
        $msg = __('eventmie-pro::em.add').' '.__('eventmie-pro::em.sub_organizer').' '.__('eventmie-pro::em.successful');
        session()->flash('status', $msg);
        
        return success_redirect($msg, $url);
    }

    /**
     *  get selected sub organizers for particular event
     */

    protected function get_sub_organizers($myevents = null)
    {
        // fetch all ids of all events
        $event_ids = $myevents->pluck('id');
        
        // get sub organizers    
        $sub_organizers = \DB::table('user_roles')->select('user_roles.*', 'users.name', 'users.email')
                            ->leftJoin('users', 'users.id', '=', 'user_roles.user_id')
                            ->whereIn('event_id', $event_ids)
                            ->where(function ($query) {
                                $query->where('user_roles.role_id', '=', 4)
                                    ->orWhere('user_roles.role_id', '=', 5)
                                    ->orWhere('user_roles.role_id', '=', 6);
                            })
                            ->get();

        // group by event_id            
        $sub_organizers   = $sub_organizers->groupBy('event_id');

        //set sub organizer for relevant event_id
        foreach($myevents as $key => $event)
        {
            $myevents[$key]['sub_organizers'] = [];
            
            //match event_id then attach sub_organizers with group by role_id
            if($sub_organizers->has($event->id))
                $myevents[$key]['sub_organizers'] = $sub_organizers[$event->id]->groupBy('role_id');
        
        };            

        return $myevents;

    }

    /**
     *    get users whose created by login organizer and they have role_id  4 or 5
     */

    public function get_organizer_users(Request $request)
    {
        $this->is_admin($request);

        $data = User::where(['organizer_id' => $this->organiser_id])
                ->where(function ($query) {
                    $query->where('role_id', '=', 4)
                        ->orWhere('role_id', '=', 5)
                        ->orWhere('role_id', '=', 6);
                })->get();

        $sub_organizers = $data->groupBy('role_id');
        
                 // group by role_id
        return response()->json(['status' => true,  'sub_organizers' => $sub_organizers ]);
    }

    /**
     *  prepare data for sub organizers then insert
     */

    protected function prepare_sub_organizers($sub_organizers = [], $event_id = null, $role_id = null)
    {
        // delete before
        \DB::table('user_roles')->where(['event_id' => $event_id, 'role_id' => $role_id])->delete();
        
        // if empty sub_organizers then return
        if(empty($sub_organizers))
            return true;

        $params = [];
        
        //prepare data
        foreach($sub_organizers as $key => $value)
        {
            $params[$key]['user_id']  = $value;
            $params[$key]['role_id']  = $role_id;
            $params[$key]['event_id'] = $event_id; 
        }

        // If the record exists, it will be updated and If the record not exists, it will be inserted
        foreach($params as $key => $value)
        {
             \DB::table('user_roles')
                ->updateOrInsert([ 
                    // check that the record exist or not base on user_id and event_id
                    'event_id' => $value['event_id'],
                    'user_id'  => $value['user_id']  
                ],
                    $value
                );  
        }            

    }

    /**
     *  organizer create user
     */

    public function organizer_create_user(Request $request)
    {
        //CUSTOM
        $this->is_admin($request);
        //CUSTOM

        // 1. validate data
        $request->validate([
            // 'organizer_id'     => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'role'             => 'required|numeric|min:4|max:6|regex:^[1-9][0-9]*$^',
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'         => ['required', 'string', 'min:8'],
        ]);

        // create user
        $user = User::create([
                    'name'          => $request->name,
                    'email'         => $request->email,
                    'password'      => \Hash::make($request->password),
                    'role_id'       =>  $request->role,
                    // 'organizer_id'  =>  $request->organizer_id,
                    //CUSTOM
                    'organizer_id'  =>  $this->organiser_id,
                    //CUSTOM
                ]);

        
         /* CUSTOM */
        // ====================== Notification ====================== 
        $mail['mail_subject']   = __('eventmie-pro::em.register_success');
        $mail['mail_message']   = __('eventmie-pro::em.get_tickets');
        $mail['action_title']   = __('eventmie-pro::em.login');
        $mail['action_url']     = route('eventmie.login');
        $mail['n_type']         = "user";

        // notification for
        $notification_ids       = [
            1, // admin
            $user->id, // new registered user
        ];
        
        $users = User::whereIn('id', $notification_ids)->get();
        
        \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'register')->delay(now()->addSeconds(10));
        /* Send email verification link */
        $user->sendEmailVerificationNotification();
        /* Send email verification link */
        // ====================== Notification ======================     
    
        return response()->json(['status' => true]);        
    } 

    /**
     *  delete seatchart image
     */

    public function delete_seatchart(Request $request)
    {
        // 1. validate data
        $request->validate([
            'event_id'                => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            
        ]);

        $params = [
            'seatingchart_image' => null, 
        ];
        
        $status   = $this->event->save_event($params, $request->event_id);

        if(empty($status))
        {
            return error('Database failure!', Response::HTTP_BAD_REQUEST );
        }
        return response()->json(['status' => true]);
    }


    /**
     * export_attendees
     */

    public function export_attendees($slug)
    {
        // check event is valid or not
        $event    = $this->event->get_event($slug);
        if(empty($event))
        {
            return error('access denied!', Response::HTTP_BAD_REQUEST );
        }
        
        $params   = [
            'event_id' => $event->id,
        ];

        // get particular event's bookings
        $bookings = $this->booking->get_event_bookings($params);
        if(empty($bookings))
            return error_redirect('Booking Not Found!');

        // customize column values
        $bookings_csv = [];
        foreach($bookings as $key => $item)
        {
            $bookings[$key]['event_repetitive'] = $item['event_repetitive'] ? __('eventmie-pro::em.yes') : __('eventmie-pro::em.no');
            $bookings[$key]['is_paid']          = $item['is_paid'] ? __('eventmie-pro::em.yes') : __('eventmie-pro::em.no');
            
            
            if($item['booking_cancel'] == 1)
                $bookings[$key]['booking_cancel']       = __('eventmie-pro::em.pending');
            elseif($item['booking_cancel'] == 2)
                $bookings[$key]['booking_cancel']       = __('eventmie-pro::em.approved');
            elseif($item['booking_cancel'] == 3)
                $bookings[$key]['booking_cancel']       = __('eventmie-pro::em.refunded');
            else
                $bookings[$key]['booking_cancel']   = __('eventmie-pro::em.no_cancellation');

            
            if($item['status'])
                $bookings[$key]['status']           = __('eventmie-pro::em.enabled');
            else
                $bookings[$key]['status']           = __('eventmie-pro::em.disabled');

            
            $bookings[$key]['checked_in']           = $item['checked_in'].' / '.$item['quantity'];

            $bookings[$key]['attendee_name']        = null;
            $bookings[$key]['attendee_email']       = null;
            $bookings[$key]['attendee_phone']       = null;
            //attendees
            if(!empty($bookings[$key]['attendees']))
            {
                $bookings[$key]['attendee_name']  = $bookings[$key]['attendees'][0]['name'];
                $bookings[$key]['attendee_email'] = $bookings[$key]['attendees'][0]['address'];
                $bookings[$key]['attendee_phone'] = $bookings[$key]['attendees'][0]['phone'];
                
            }
        }    

        // convert array to collection for csv
        $bookings = collect($bookings);

        // create object of laracsv
        $csvExporter = new \Laracsv\Export();
    
        // create csv 
        $csvExporter->build($bookings, [
            
            //events fields which will be include
            'id',
            
            'itm_sku',
            'event_category',
            'event_title',
            'event_start_date',
            'event_end_date',
            'event_start_time',
            'event_end_time',
            'event_repetitive',

            'customer_name', 
            'customer_email', 

            'attendee_name',
            'attendee_email',
            'attendee_phone',

            'order_number',
            'ticket_title',
            'ticket_price',
            'price',
            'quantity', 
            'tax',
            'net_price',
            'currency',
            'transaction_id',
            'is_paid',
            'payment_type',
            
            'booking_cancel',
            'status',
            'checked_in',

            'created_at', 
            'updated_at'
        ]);
        
        // download csv
        $csvExporter->download($event->slug.'-attendies.csv');
    } 
    
}
