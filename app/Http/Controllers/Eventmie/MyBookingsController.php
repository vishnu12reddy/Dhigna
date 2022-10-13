<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\MyBookingsController as BaseMyBookingsController;
use App\Models\Booking;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class MyBookingsController extends BaseMyBookingsController
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
        $this->booking      = new Booking;
        // CUSTOM
    
    }

    /**
     * Show my booking
     *
     * @return array
     */
    public function index($view = 'vendor.eventmie-pro.bookings.customer_bookings', $extra = [])
    {
        return parent::index($view, $extra);    
    }

    public function get_customer_events(Request $request)
    {
        $events = Booking::distinct()->select(['event_id', 'event_title'])->where(['customer_id' => Auth::id() ])->get();
        
        return response()->json(['events' => $events, 'status' => true]);
    }

    // get bookings by customer id
    public function mybookings(Request $request)
    {
        $params     = [
            'customer_id'       => Auth::id(),
            //CUSTOM
            'start_date'        => !empty($request->start_date) ? $request->start_date : null,
            'end_date'          => !empty($request->end_date) ? $request->end_date : null,
            'event_id'          => (int)$request->event_id,
            'event_start_date'  => !empty($request->event_start_date) ? $request->event_start_date : null,
            'event_end_date'    => !empty($request->event_end_date) ? $request->event_end_date : null,
            'search'            => $request->search,
            'length'            => $request->length,
            
            //CUSTOM
        ];

        //CUSTOM
        // in case of today and tomorrow and weekand
        if($request->event_start_date == $request->event_end_date)
            $params['event_end_date']     = null;
     
        $bookings    = $this->booking->get_my_bookings($params);
        //CUSTOM


        $bookings    = $this->booking->get_my_bookings($params);

        return response([
            'bookings'  => $bookings->jsonSerialize(),
            'currency'  => setting('regional.currency_default'),
        ], Response::HTTP_OK);

    }

    // booking cancellation
    public function cancel(Request $request)
    {
        if(!empty(setting('booking.disable_booking_cancellation')))
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );


        $request->validate([
            'event_id'           => 'required|numeric',
            'ticket_id'          => 'required|numeric',
            'booking_id'         => 'required|numeric',
        ]);

        $params = [
            'event_id'    => $request->event_id,
            'ticket_id'   => $request->ticket_id,
            'booking_id'  => $request->booking_id,
            'customer_id' => Auth::id(),
        ];

        // get event by event_id
        $event              = $this->event->get_event(null, $request->event_id);

        if(empty($event))
            return error(__('eventmie-pro::em.event').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );

        // check booking id in booking table for login user
        $check_booking     = $this->booking->check_booking($params);
        
        if(empty($check_booking))
            return error(__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );

        $start_date              = Carbon::parse($check_booking['event_start_date'].' '.$check_booking['event_start_time']);
        $end_date                = Carbon::parse(Carbon::now());
        
        // check date expired or not
        if($end_date > $start_date)
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );

        // pre booking time cancellation check    
        $pre_cancellation_time  = (float) setting('booking.pre_cancellation_time'); 
        $min                    = number_format((float)($start_date->diffInMinutes($end_date) ), 2, '.', '');
        $hour_difference        = (float)sprintf("%d.%02d", floor($min/60), $min%60);
        
        if($pre_cancellation_time > $hour_difference)
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );

        // booking cancellation
        $booking_cancel    = $this->booking->booking_cancel($params);

        if(empty($booking_cancel))
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );

        /* use updated booking data */
        $check_booking->booking_cancel = 1;
        
        // ====================== Notification ====================== 
        //send notification after bookings
        $msg[]                  = __('eventmie-pro::em.customer').' - '.$check_booking->customer_name;
        $msg[]                  = __('eventmie-pro::em.email').' - '.$check_booking->customer_email;
        $msg[]                  = __('eventmie-pro::em.event').' - '.$check_booking->event_title;
        $msg[]                  = __('eventmie-pro::em.category').' - '.$check_booking->event_category;
        $msg[]                  = __('eventmie-pro::em.ticket').' - '.$check_booking->ticket_title;
        $msg[]                  = __('eventmie-pro::em.price').' - '.$check_booking->ticket_price;
        $msg[]                  = __('eventmie-pro::em.order').' - #'.$check_booking->order_number;
        $msg[]                  = __('eventmie-pro::em.status').' - '.($check_booking->status ? __('eventmie-pro::em.enabled') : __('eventmie-pro::em.disabled'));
        $msg[]                  = __('eventmie-pro::em.payment').' - '.($check_booking->is_paid ? __('eventmie-pro::em.paid') : __('eventmie-pro::em.unpaid'));
        $cancellation_msg           = __('eventmie-pro::em.no_cancellation');
        if($check_booking->booking_cancel == 1)
            $cancellation_msg       = __('eventmie-pro::em.pending');
        elseif($check_booking->booking_cancel == 2)
            $cancellation_msg       = __('eventmie-pro::em.approved');
        elseif($check_booking->booking_cancel == 3)
            $cancellation_msg       = __('eventmie-pro::em.refunded');

        $msg[]                  = __('eventmie-pro::em.cancellation').' - '.$cancellation_msg;
        $extra_lines            = $msg;

        $mail['mail_subject']   = __('eventmie-pro::em.booking_cancellation_pending');
        $mail['mail_message']   = __('eventmie-pro::em.booking_cancellation_processing');
        $mail['action_title']   = __('eventmie-pro::em.mybookings');
        $mail['action_url']     = route('eventmie.mybookings_index');
        $mail['n_type']       =  "cancel";

        /* CUSTOM */
        $mail['extra_lines']    = $extra_lines;
        /* CUSTOM */

        $notification_ids       = [1, Auth::id(), $check_booking->organiser_id];
        
        $users = User::whereIn('id', $notification_ids)->get();
        try {
            // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail, $extra_lines));
            //CUSTOM
            \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'edit_booking')->delay(now()->addSeconds(10));
            //CUSTOM
        } catch (\Throwable $th) {}
        // ====================== Notification ======================
        

        return response([
            'status'=> true,
        ], Response::HTTP_OK);
        
    }

}
