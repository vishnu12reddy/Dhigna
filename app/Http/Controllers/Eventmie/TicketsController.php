<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\TicketsController as BaseTicketsController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Ticket;

/* CUSTOM */
use App\Models\Promocode;
/* CUSTOM */

class TicketsController extends BaseTicketsController
{
    public function __construct()
    {
        parent::__construct();
        $this->promocode = new Promocode; 
        $this->ticket    = new Ticket;   
    }

    // add/edit tickets
    public function store(Request $request)
    {
        // if logged in user is admin
        $this->is_admin($request);

        // float validation and don't except nagitive value
        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";
        // 1. validate data
        $request->validate([
            
            'title'         => 'required|max:64',
            'price'         => ['required','regex:'.$regex],
            'quantity'      => 'required|integer|min:0',
            'description'   => 'max:512',
            'event_id'      => 'required|numeric',
            'customer_limit' => 'nullable|integer|gt:0'
        ]);     

        // check event id with login user that event id valid or not
        $check_event            = $this->event->get_user_event($request->event_id, $this->organiser_id);

        if(empty($check_event))
        {
            return error('access denied!', Response::HTTP_BAD_REQUEST );
        }

        $params    = [
            'event_id' =>  $request->event_id,
        ];
        
        $params = [
            "title"        => $request->title,
            "price"        => $request->price,
            "quantity"     => $request->quantity,
            "description"  => $request->description,
            "event_id"     => $request->event_id,
            //CUSTOM
            "t_soldout"     => !empty($request->t_soldout) ? 1 : 0,
            "is_donation"     => !empty($request->is_donation) ? 1 : 0,
            //CUSTOM
        ];

        
        //CUSTOM
        
        $sale_ticket   =  Ticket::where(['id' => $request->ticket_id, 'event_id' => $request->event_id])->first(); 

        if(empty($sale_ticket))
        {
            $sale_ticket                       = (object) [];
            $sale_ticket->sale_start_date      = null;
            $sale_ticket->sale_end_date        = null;
        }

        if(!empty($request->sale_start_date) || !empty($request->sale_end_date) || !empty($request->sale_price))
        {
            if($sale_ticket->sale_start_date != $request->sale_start_date || empty($sale_ticket->sale_start_date))
            {
                $request->validate([
                    'sale_start_date'        => 'required|date|date_format:Y-m-d H:i:s',
                    
                ]);
            } 

            if($sale_ticket->sale_end_date != $request->sale_end_date || empty($sale_ticket->sale_end_date))
            {
                $request->validate([
                    'sale_end_date'          => 'required|date|after:sale_start_date|date_format:Y-m-d H:i:s',
                    
                ]);
            } 
            
            $request->validate([
                'sale_price'                  => ['required','regex:'.$regex],
            ]);
        
        }
        
        $params['sale_start_date']   = $request->sale_start_date;
        $params['sale_end_date']     = $request->sale_end_date;
        $params['sale_price']        = $request->sale_price;
        //CUSTOM
    

        $params['customer_limit'] = empty($request->customer_limit) ? null : $request->customer_limit;

        $ticket_id  = $request->ticket_id;

        $ticket     =  $this->ticket->add_tickets($params, $ticket_id);
        
        if(empty($ticket))
        {
            return response()->json(['status' => false]);    
        }

        // add data in tax_ticket pivot table
        $taxes_ids    = json_decode($request->taxes_ids, true);
        $ticket->taxes()->sync($taxes_ids);

        // if have tickets then check free tickets or not
        $tickets   = $this->ticket->get_event_tickets($params);
        
        if($tickets->isNotEmpty())
        {
            // check free tickets        
            $free_tickets           = $this->ticket->check_free_tickets($request->event_id);
            
            if(!empty($free_tickets) || (int)$request->price <= 0)
            {
                $params = [
                    'price_type' => 0
                ];

                // update price type column of event table by 1
                $this->event->update_price_type($request->event_id, $params);
            }
            else
            {
                $params = [
                    'price_type' => 1
                ];
                // update price type column of event table by 0
                $this->event->update_price_type($request->event_id, $params);
            }
        }  

        /* CUSTOM */
        // Update ticket_id in case of new ticket
        if(!$ticket_id)
            $ticket_id = $ticket->id;
            
        // save promocodes
        $this->save_promocode($request, $ticket_id);
        /* CUSTOM */

        // get update event
        $event            = $this->event->get_user_event($request->event_id, $this->organiser_id);
        // set step complete
        $this->complete_step($event->is_publishable, 'tickets', $request->event_id);

        return response()->json(['status' => true]);
        
    }

    // get tickets by events
    public function tickets(Request $request)
    {
        // 1. validate data
        $request->validate([
            'event_id'          => 'required',
        ]);
        
        // if logged in user is admin
        $this->is_admin($request);

        $check_event    = $this->event->get_user_event($request->event_id, $this->organiser_id);

        if(empty($check_event))
        {
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        }

        
        //CUSTOM
        $currency = setting('regional.currency_default');

        if(!empty($check_event->currency))
        {
            $currency = $check_event->currency;
        }
        //CUSTOM

        $params    = [
            'event_id' =>  $request->event_id,
        ];
        $tickets   = $this->ticket->get_event_tickets($params);
        
        if($tickets->isEmpty())
        {
            return response()->json(['status' => false, 'currency' => setting('regional.currency_default')]);    
        }

        return response()->json(['tickets' => $tickets, 'status' => true, 'currency' => $currency]);
    }

    /**
     *  custom function start
     */

    // save promocode 
    public function save_promocode(Request $request, $ticket_id = null)
    {
        // save promocodes
        $promocodes = [];
        $params     = [];

        if(!empty($request->promocodes_ids))
        {
            $promocodes = explode (",", $request->promocodes_ids);
         
            foreach($promocodes as $key => $value)
            {
                $params[$key]['ticket_id']    =  $ticket_id;
                $params[$key]['promocode_id'] =  $value;
            }
        }
        $this->promocode->save_ticket_promocode($params, $ticket_id);
    } 
}
