<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\TicketScanController as BaseTicketScanController;
use App\Models\Attendee;
use App\Models\ScannerModel;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Auth;

class TicketScanController extends BaseTicketScanController
{
    public function __construct()
    {
        $this->middleware('scanner');
        $this->booking      = new Booking;
        $this->scanner      = new ScannerModel;
    }

    // ticket scan
    public function index(Request $request, $view = 'vendor.eventmie-pro.ticket_scan.index', $extra = [])
    {
        return parent::index($request, $view, compact('extra'));
    }

    public function get_booking(Request $request)
    {
        $request->validate([
            'id'            => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'order_number'  => 'required',
        ]);

        $organiser_id = Auth::user()->organizer_id;
    
        // so that we can pass organizer other than logged in user
        if(!$organiser_id)
            $organiser_id = Auth::id();
    
        
        //  CUSTOM
        
        // get related event's ids for scanner organizer 
        if(Auth::user()->hasRole('scanner'))
        {
            $event_ids   = $this->scanner->get_scanner_event_ids(Auth::id())->all();

            if(empty($event_ids))
            {
                return response()->json([
                        'errors' => [
                            'msg' => [__('eventmie-pro::em.unauthorized')]
                        ]
                ], 422);
            }
            
            $booking = Booking::where(['id' => $request->id])->first();

            if(empty($booking))
            {
                return response()->json([
                        'errors' => [
                            'msg' => [__('eventmie-pro::em.ticket').' '.__('eventmie-pro::em.not_found')]
                        ]
                ], 422);
            }

            if(!in_array($booking->event_id, $event_ids))
            {
                return response()->json([
                        'errors' => [
                            'msg' => [__('eventmie-pro::em.unauthorized')]
                        ]
                ], 422);
            }

            $event  = Event::where(['id' => $booking->event_id, 'user_id' => $organiser_id ])->first();

            if(empty($event))
            {
                return response()->json([
                        'errors' => [
                            'msg' => [__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found')]
                        ]
                ], 422);
            }
        
        }
        
        if(Auth::user()->hasRole('admin'))
        {
            $booking = Booking::with(['attendees'])->where(['id'=>$request->id, 'order_number'=>$request->order_number])->first();
            
        }
        else
        {
            $booking = Booking::with(['attendees'])->where(['id'=>$request->id, 'order_number'=>$request->order_number, 'organiser_id' => $organiser_id])->first();

        }

        if(empty($booking))
        {
            
            return response()->json([
                    'errors' => [
                        'msg' => [__('eventmie-pro::em.ticket').' '.__('eventmie-pro::em.not_found')]
                    ]
            ], 422);
        
        }
        
        //  CUSTOM

        return response()->json(['status' => true, 'booking' => $booking ]);
    }

    // verify tikcet after scan
    public function verify_ticket(Request $request, $organiser_id = null)
    {
        $request->validate([
            'booking_id'          => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            'order_number'        => 'required',
            //CUSTOM
            'attendee_id'         => 'required|numeric|min:1|regex:^[1-9][0-9]*$^',
            //CUSTOM
        ]);
        
        $params = [
            'id'            => $request->booking_id,
            'order_number'  => $request->order_number,
        ];

        //  CUSTOM

        //CUSTOM
        $organiser_id = Auth::user()->organizer_id;
        //CUSTOM

        // so that we can pass organizer other than logged in user
        if(!$organiser_id)
            $organiser_id = Auth::id();
        
        
        // get related event's ids for scanner organizer 
        if(Auth::user()->hasRole('scanner'))
        {
            $event_ids   = $this->scanner->get_scanner_event_ids(Auth::id())->all();

            if(empty($event_ids))
            {
                $msg = __('eventmie-pro::em.ticket').' '.__('eventmie-pro::em.unauthorized');
                session()->flash('error', $msg);
                return error_redirect($msg);
            }
            
            $booking = Booking::where(['id' => $request->booking_id])->first();

            if(empty($booking))
            {
                $msg = __('eventmie-pro::em.ticket').' '.__('eventmie-pro::em.not_found');
                session()->flash('error', $msg);
                return error_redirect($msg);
            }

            if(!in_array($booking->event_id, $event_ids))
            {
                $msg = __('eventmie-pro::em.unauthorized');
                session()->flash('error', $msg);
                return error_redirect($msg);
            }
    
            $event  = Event::where(['id' => $booking->event_id, 'user_id' => $organiser_id ])->first();

            if(empty($event))
            {
                $msg = __('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found');
                session()->flash('error', $msg);
                return error_redirect($msg);
            }

        }


        // check for organizer id except for Admin
        if(!Auth::user()->hasRole('admin'))
            $params['organiser_id'] = $organiser_id;
        
        // check booking 
        // if it's organizer's booking
        // and ticket already scan or not
        $booking = $this->booking->organiser_check_booking($params);

        // ticket already scan then show error message
        if(empty($booking))
        {
            $msg = __('eventmie-pro::em.ticket').' '.__('eventmie-pro::em.not_found');
            session()->flash('error', $msg);
            return error_redirect($msg);
        }

        if($booking->status != 1) 
        {
            $msg = __('eventmie-pro::em.disabled_ticket');
            session()->flash('error', $msg);
            return error_redirect($msg);
        }

        if($booking->is_paid != 1) 
        {
            $msg = __('eventmie-pro::em.disabled_ticket');
            session()->flash('error', $msg);
            return error_redirect($msg);
        }

        if($booking->checked_in == $booking->quantity) 
        {
            $msg = __('eventmie-pro::em.already_cheked_in');
            session()->flash('error', $msg);
            return error_redirect($msg);
        }


        $data = [
            'checked_in' => $booking->checked_in + 1,
            'scanner_id' => (Auth::user()->hasRole('scanner') ? Auth::id() : null),
        ];

        //CUSTOM
        $attendee = Attendee::where(['id' => $request->attendee_id, 'checked_in' => 0 ])->first();
        
        
        if(empty($attendee))
        {
            $msg = __('eventmie-pro::em.already_cheked_in');
            session()->flash('error', $msg);
            return error_redirect($msg);
        }

        $attendee->update(['checked_in' => 1]);
        //CUSTOM

        // update checked_in by 1        
        $booking = $this->booking->organiser_edit_booking($data, $params);
        
        $url = route('eventmie.ticket_scan');
        $msg = __('eventmie-pro::em.success_cheked_in');
        
        session()->flash('status', $msg);
        return success_redirect($msg, $url);
    
    }
}
