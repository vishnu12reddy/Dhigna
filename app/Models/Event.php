<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Classiebit\Eventmie\Models\Event as BaseEvent;
use Carbon\Carbon;
use DB;
use Auth;
use App\Models\Ticket;
use App\Models\Review;
use App\Models\User;

class Event extends BaseEvent
{
    use HasFactory;
    protected $hidden  = ['online_location', 'youtube_embed', 'vimeo_embed', 'event_password'];

     /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'video_link' => 'array',
    ];

    /**
     * Get events with 
     * pagination and custom selection
     * 
     * @return string
     */
    public function events($params  = [])
    {   
        $query = Event::query(); 
        
        $query
        ->leftJoin("categories", "categories.id", '=', "events.category_id")
        ->select(["events.*", "categories.name as category_name"]);

        if(!empty($params['search']))    
        {
            $query
            ->whereRaw("( title LIKE '%".$params['search']."%' 
                OR venue LIKE '%".$params['search']."%' OR state LIKE '%".$params['search']."%' OR city LIKE '%".$params['search']."%')");
        }

        if(!empty($params['city']))
        {
            $query
            ->where('city','LIKE',"%{$params['city']}%");
        }

        if(!empty($params['state']))
        {
            $query
            ->where('state','LIKE',"%{$params['state']}%");
        }
            
        $query
        ->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
        ->selectRaw("(SELECT SD.repetitive_type  FROM schedules SD WHERE SD.event_id = events.id limit 1 ) repetitive_type");
        
        if(!empty($params['category_id']))
            $query->where('category_id',$params['category_id']);

        if(!empty($params['country_id']))
            $query->where('country_id',$params['country_id']);

 
        if(!empty($params['start_date']) && !empty($params['end_date']))
        {
            $query->whereRaw('CASE WHEN repetitive = 1 THEN start_date <= "'.$params['start_date'].'" 
            AND end_date >= "'.$params['end_date'].'" ELSE  start_date >= "'.$params['start_date'].'" END' );
        }
        
    
        if(!empty($params['price']))
        {
            if($params['price'] == 'free')
                $query->where('price_type', "0" );
            
            if($params['price'] == 'paid')
                $query->where('price_type', 1);    
        }

        $query
        ->where(["events.status" => 1, "events.publish" => 1, "categories.status" => 1]);
        
        // if hide expired events is on
        if(!empty(setting('booking.hide_expire_events')))
        {
            $today  = \Carbon\carbon::now(setting('regional.timezone_default'))->format('Y-m-d');    
            $query->whereRaw('(IF(events.repetitive = 1, events.end_date >= "'.$today.'", events.start_date >= "'.$today.'"))');
        }

        // CUSTOM
        $query->whereNull('event_password');
        $query->where(['is_private' => 0]);

        if(Auth::check())
        {
            if(Auth::user()->hasRole('pos'))
            {
                $event_ids = $this->get_pos_event_ids();
                
                $query->whereIn('events.id', $event_ids);
            }
        }
        
        // CUSTOM

        return $query->orderBy('events.updated_at', 'DESC')->paginate(9);
    }

    // get featured event for welocme page
    public function get_featured_events()
    {
        $query = Event::query();

        $query->leftJoin("categories", "categories.id", '=', "events.category_id")
                ->select(["events.*", "categories.name as category_name"])
                ->where(['events.featured' => 1, 'events.publish' => 1, 'events.status' => 1, 'categories.status' => 1])
                ->whereDate('end_date', '>=', Carbon::today()->toDateString())
                // CUSTOM
                ->whereNull('event_password')
                ->where(['is_private' => 0]);
               
                if(Auth::check())
                {
                    if(Auth::user()->hasRole('pos'))
                    {
                        $event_ids = $this->get_pos_event_ids();
                        
                        $query->whereIn('events.id', $event_ids);
                    }
                }
                // CUSTOM
                
        return $query->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
                ->selectRaw("(SELECT SD.repetitive_type  FROM schedules SD WHERE SD.event_id = events.id limit 1 ) repetitive_type")
                ->limit(6)
                ->get();
    }
    
    // get top selling event
    public function get_top_selling_events()
    {
        $query = Event::query();

        $query->leftJoin("categories", "categories.id", '=', "events.category_id")
                ->select(["events.*", "categories.name as category_name"])
                ->selectRaw("(SELECT SUM(BK.quantity) FROM bookings BK WHERE BK.event_id = events.id) total_booking")
                ->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
                ->selectRaw("(SELECT SD.repetitive_type  FROM schedules SD WHERE SD.event_id = events.id limit 1 ) repetitive_type")
                ->where(['events.publish' => 1, 'events.status' => 1, 'categories.status' => 1])
                // CUSTOM
                ->whereNull('event_password')
                ->where(['is_private' => 0]);

                if(Auth::check())
                {
                    if(Auth::user()->hasRole('pos'))
                    {
                        $event_ids = $this->get_pos_event_ids();
                        
                        $query->whereIn('events.id', $event_ids);
                    }
                }
                // CUSTOM
                
            return $query->whereDate('end_date', '>=', Carbon::today()->toDateString())
                ->orderBy('total_booking', 'desc')
                ->limit(6)
                ->get();
    }
    
    // get upcomming events
    public function get_upcomming_events()
    {
        $query = Event::query();

        $query->leftJoin("categories", "categories.id", '=', "events.category_id")
                    ->select(["events.*", "categories.name as category_name"])
                    ->whereDate('start_date', '!=', Carbon::now()->format('Y-m-d'))
                    ->whereDate('start_date', '>', Carbon::now()->format('Y-m-d'))
                    ->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
                    ->selectRaw("(SELECT SD.repetitive_type  FROM schedules SD WHERE SD.event_id = events.id limit 1 ) repetitive_type")
                    ->where(['events.publish' => 1, 'events.status' => 1, 'categories.status' => 1])
                    ->whereDate('end_date', '>', Carbon::today()->toDateString())
                    // CUSTOM
                    ->whereNull('event_password')
                    ->where(['is_private' => 0]);
                    if(Auth::check())
                    {
                        if(Auth::user()->hasRole('pos'))
                        {
                            $event_ids = $this->get_pos_event_ids();
                            
                            $query->whereIn('events.id', $event_ids);
                        }
                    }
                    // CUSTOM
                    
                return $query->orderBy('start_date')
                    ->limit(6)
                    ->get();

    }

    // get my evenst of particular organiser 
    public function get_my_events($params = [])
    {
        //CUSTOM
        $events       = new Event();
        $table        = $events->getTable();
        
        //GET ALL COLUMNS
        $columns      = \Schema::getColumnListing($table);
        
        $length       = $params['length'];
        
        $query = Event::query();
        
        //CUSTOM
        // return Event::select('events.*')
        $query->select('events.*')
            ->from('events')
            ->selectRaw("(SELECT CN.country_name FROM countries CN WHERE CN.id = events.country_id) country_name")
            ->selectRaw("(SELECT CT.name FROM categories CT WHERE CT.id = events.category_id) category_name")
            ->selectRaw("(SELECT COUNT(BK.id) FROM bookings BK WHERE BK.event_id = events.id  ) count_bookings")
            ->where(['user_id' => $params['organiser_id'] ]);
            
            
            // CUSTOM
            if(!empty($params['search']))
            {
                // ALL COLUMNS SEARCH
                
                    $query->where(function($query) use($columns, $params) {
                        foreach($columns as $key => $column)
                        {
                            $query->orWhere('events.'.$column, 'like', '%' . $params['search'] . '%' );
                        }
                    });
                    
                
            }
            
            // ->paginate(10);
                
            return    $query->paginate($length);
            // CUSTOM
    }

    // search customers
    public function search_customers($email = null)
    {
        $query = DB::table('users'); 
        $query->select('name', 'id', 'email', 'phone')
                ->where('role_id', 2)
                ->where('email', $email);
        
        $result = $query->get();
        return to_array($result);
    }

    // get related event_ids for pos organizer 
    public function get_pos_event_ids()
    {
        
        $login_id = Auth::user()->id;
                
        return \DB::table('user_roles')->where(['user_id' => $login_id])->pluck('event_id');
    }

    public function tickets(){

        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the reviews for the event .
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the reviews for the event .
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    
}
