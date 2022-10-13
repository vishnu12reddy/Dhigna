<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\WelcomeController as BaseWelcomeController;
use App\Models\Event;
use Carbon\Carbon;
use Classiebit\Eventmie\Models\Ticket;


class WelcomeController extends BaseWelcomeController
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
        // CUSTOM
    
    }

    // get featured events
    public function index($view = 'vendor.eventmie-pro.welcome', $extra = [])
    {
        
        $featured_events     = $this->get_featured_events();
        $top_selling_events  = $this->get_top_selling_events();
        $upcomming_events    = $this->get_upcomming_events();
        $banners             = $this->banner->get_banners();
        $categories          = $this->category->get_categories();
        $currency            = setting('regional.currency_default');
        $cities_events       = $this->event->get_cities_events();
        
        //get blog for welcome page
        $posts               = $this->post->index();
        
        //CUSTOM

        $featured_events     = collect($this->get_featured_events());
        $top_selling_events  = collect($this->get_top_selling_events());
        $upcomming_events    = collect($this->get_upcomming_events());

        // sale tickets
        if($featured_events->isNotEmpty())
        {
            $featured_events = $this->addSaleTickets($featured_events);
        }

        if($top_selling_events->isNotEmpty())
        {
            $top_selling_events = $this->addSaleTickets($top_selling_events);
        }

        if($upcomming_events->isNotEmpty())
        {
            $upcomming_events = $this->addSaleTickets($upcomming_events);
        }
        
        //CUSTOM
        
        return view($view, 
            compact(
                'featured_events', 'top_selling_events', 
                'upcomming_events', 'banners',
                'categories', 'posts', 'currency', 'cities_events', 
                'extra'
            ));
            
    }
    
    //CUSTOM
    protected function addSaleTickets($events = null)
    {   
        $events = $events->map(function ($event, $key) {
                
            $event->sale_tickets = Ticket::where('sale_start_date', '<=', Carbon::now()->timezone(setting('regional.timezone_default'))->toDateTimeString())
            ->where('sale_end_date', '>', Carbon::now()->timezone(setting('regional.timezone_default'))->toDateTimeString())
            ->whereNotNull('sale_start_date')
            ->where(['event_id' => $event->id])
            ->orderBy('sale_start_date')
            ->get();    
           
            return $event;
        });

        return $events;
    }
    //CUSTOM
}
