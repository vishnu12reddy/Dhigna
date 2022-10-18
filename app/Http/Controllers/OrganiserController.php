<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use Classiebit\Eventmie\Models\Event;
use Carbon\Carbon;

class OrganiserController extends Controller
{

    public function __construct()
    {
        $this->middleware(['common']);

        $this->ticket = new Ticket;
    }

    /**
     * show all events of particular organiser
     */

    public function show($event = null, $name = null)
    {
        if(empty($name))
            return abort('404');
        
        $name           = (string) $name;
        $organiser      =  User::whereRaw( '`name` LIKE ?', [ $name ] )->first();
       
        if(empty($organiser))
            return abort('404');
            
        $events  = Event::leftJoin("categories", "categories.id", '=', "events.category_id")
                        ->select(["events.*", "categories.name as category_name"])
                        ->where(['events.user_id' => $organiser['id'], 'events.publish' => 1, 
                                    'events.status'   => 1, 'categories.status' => 1])
                        ->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
                        ->selectRaw("(SELECT SD.repetitive_type  FROM schedules SD WHERE SD.event_id = events.id limit 1 ) 
                            repetitive_type")
                        ->selectRaw("(SELECT U.name FROM users U WHERE U.id = events.user_id) organiser_name")->get();

        $event_ids       = [];

        foreach($events as $key => $value)
            $event_ids[] = $value->id;

        // pass events ids
        // tickets
        $events_tickets     = $this->ticket->get_events_tickets($event_ids);
                
        $events_data             = [];
        
        foreach($events as $key => $value)
        {
            // online event - yes or no
            $value                  = $value->makeVisible('online_location');
            // check event is online or not
            $value->online_location    = (!empty($value->online_location)) ? 1 : 0; 

            $events_data[$key]             = $value;
            
            foreach($events_tickets as $key1 => $value1)
            {
                // check relevant event_id with ticket id
                if($value->id == $value1['event_id'])
                {
                    $events_data[$key]->tickets[]       = $value1;
                }
            }

            //CUSTOM
            // sale tickets
            $sale_tickets = Ticket::where('sale_start_date', '<=', Carbon::now()->timezone(setting('regional.timezone_default'))->toDateTimeString())
            ->where('sale_end_date', '>', Carbon::now()->timezone(setting('regional.timezone_default'))->toDateTimeString())
            ->whereNotNull('sale_start_date')
            ->where(['event_id' => $value->id])
            ->orderBy('sale_start_date')
            ->get();    

            $events_data[$key]->sale_tickets  = $sale_tickets;
            //CUSTOM
        }
        
        $events = collect($events_data);
        
        $activeEvents   = [];
        $expiredEvents  = [];
        
        if($events->isNotEmpty())
        {
             // for active events
            $activeEvents = $events->reject(function ($value, $key) {
                
                // for without repeative event 
                if($value->repetitive == 0)
                    return Carbon::today()->toDateString() > $value->start_date;

                // for repeative event 
                return Carbon::today()->toDateString() > $value->end_date;
            });


            // for expired events
            $expiredEvents = $events->reject(function ($value, $key) {
                
                // for without repeative event 
                if($value->repetitive == 0)
                    return Carbon::today()->toDateString() < $value->start_date;
                    
                // for repeative event 
                return Carbon::today()->toDateString() < $value->end_date;
            });
            
        }    

        $currency            = setting('regional.currency_default');

        $organiser_d          = (object) [
            'id'                => $organiser->id,
            'name'              => $organiser->name,
            'email'             => $organiser->email,
            'avatar'            => $organiser->avatar,
            'organisation'      => $organiser->organisation,
            'address'           => $organiser->address,
            'phone'             => $organiser->phone,
            'org_description'   => $organiser->org_description,
            'org_facebook'      => $organiser->org_facebook,
            'org_instagram'     => $organiser->org_instagram,
            'org_youtube'       => $organiser->org_youtube,
            'org_twitter'       => $organiser->org_twitter,
        ];
        
        
        return view('organiser.show',compact(['activeEvents', 'expiredEvents', 'currency', 'organiser_d']));
    }

    
}
