<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\OBookingsController as BaseOBookingsController;
use App\Models\Booking;
use App\Models\Event;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\User;

class OBookingsController extends BaseOBookingsController
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
        $this->event        = new Event;
        // CUSTOM
    
    }
    
    /**
     * Show my booking
     *
     * @return array
     */
    public function index($view = 'vendor.eventmie-pro.bookings.organiser_bookings', $extra = [])
    {
        // show organiser_bookings
        return parent::index($view, $extra);
    }

    /**
     * show promocode on view booking
     */
    public function organiser_bookings_show($id = null, $view = 'vendor.eventmie-pro.bookings.show', $extra = [])
    {
        return parent::organiser_bookings_show($id, $view, $extra);
    }

    /**
     * Show organiser bookings
     *
     * @return array
     */
    public function organiser_bookings(Request $request)
    {
        $params     = [
            'organiser_id'  => Auth::id(),
            'start_date'    => !empty($request->start_date) ? $request->start_date : null,
            'end_date'      => !empty($request->end_date) ? $request->end_date : null,
            'event_id'      => (int)$request->event_id,

            
            //CUSTOM
            'event_start_date'    => !empty($request->event_start_date) ? $request->event_start_date : null,
            'event_end_date'      => !empty($request->event_end_date) ? $request->event_end_date : null,
            'search'            => $request->search,
            'length'            => $request->length,
           
            //CUSTOM
        ];

        // in case of today and tomorrow and weekand
        if($request->start_date == $request->end_date)
            $params['end_date']     = null;

        //CUSTOM
        // in case of today and tomorrow and weekand
        if($request->event_start_date == $request->event_end_date)
            $params['event_end_date']     = null;
        //CUSTOM
    
        $bookings    = $this->booking->get_organiser_bookings($params);
        
        return response([
            'bookings'  => $bookings->jsonSerialize(),
            'currency'  => setting('regional.currency_default'),
        ], Response::HTTP_OK);
    }

    // booking edit for customer by organiser
    public function organiser_bookings_edit(Request $request)
    {
        $request->validate([
            'event_id'           => 'required|numeric',
            'ticket_id'          => 'required|numeric',
            'booking_id'         => 'required|numeric',
            'customer_id'        => 'required|numeric',
            'booking_cancel'     => 'required|numeric',
            'status'             => 'numeric|nullable',
            'is_paid'            => 'numeric|nullable',
        ]);

        $params = [
            'event_id'         => $request->event_id,
            'ticket_id'        => $request->ticket_id,
            'id'               => $request->booking_id,
            'organiser_id'     => Auth::id(),
            'customer_id'      => $request->customer_id,
        ];

        // check booking id in booking table for organiser
        $check_booking     = $this->booking->organiser_check_booking($params);

        if(empty($check_booking))
            return error(__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );
        
        $start_date              = Carbon::parse($check_booking['event_start_date'].' '.$check_booking['event_start_time']);
        $end_date                = Carbon::parse(Carbon::now());
        
        // check date expired or not
        if($end_date > $start_date)
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );

        // pre booking time cancellation check    
        $pre_cancellation_time   = (float) setting('booking.pre_cancellation_time'); 
        $min                     = number_format((float)($start_date->diffInMinutes($end_date) ), 2, '.', '');
        $hour_difference         = (float)sprintf("%d.%02d", floor($min/60), $min%60);
        
        if($pre_cancellation_time > $hour_difference)
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );

        $params = [
            'event_id'         => $request->event_id,
            'ticket_id'        => $request->ticket_id,
            'id'               => $request->booking_id,
            'organiser_id'     => Auth::id(),
            'customer_id'      => $request->customer_id,
        ];

        $data = [
            'booking_cancel'   => $request->booking_cancel,
            'status'           => $request->status ? $request->status : 0 ,
            
            // is_paid
            'is_paid'          =>  $request->is_paid,
        ];
        // booking edit
        $booking_edit    = $this->booking->organiser_edit_booking($data, $params);

        if(empty($booking_edit))
            return error(__('eventmie-pro::em.booking_cancellation_fail'), Response::HTTP_BAD_REQUEST );


        $params = [
            'booking_id'       => $request->booking_id,
            'organiser_id'     => Auth::id(),
            'status'           => $request->status ? $request->status : 0,
        ];
       
        // edit commision table status when change booking table status change by organiser 
        $edit_commission  = $this->commission->edit_commission($params);    

        if(empty($edit_commission))
            return error(__('eventmie-pro::em.commission').' '.__('eventmie-pro::em.not_found'), Response::HTTP_BAD_REQUEST );

        /* use updated booking data */
        $check_booking->booking_cancel = $data['booking_cancel'];
        $check_booking->status         = $data['status'];
        $check_booking->is_paid        = $data['is_paid'];
        
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

        $mail['mail_subject']   = __('eventmie-pro::em.booking_update');
        $mail['mail_message']   = __('eventmie-pro::em.booking_status');
        $mail['action_title']   = __('eventmie-pro::em.mybookings');
        $mail['action_url']     = route('eventmie.mybookings_index');
        $mail['n_type']       = "cancel";

        /* CUSTOM */
        $mail['extra_lines']    = $extra_lines;
        /* CUSTOM */

        
        $notification_ids       = [1, $check_booking->organiser_id, $check_booking->customer_id];
        
        $users = User::whereIn('id', $notification_ids)->get();
        try {
            // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail, $extra_lines));
            //CUSTOM
            \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'edit_booking')->delay(now()->addSeconds(10));
            // test
            // return view('email_templates.editBooking', compact('mail'));
            //CUSTOM
        } catch (\Throwable $th) {}
        // ====================== Notification ======================  
        
        return response([
            'status'=> true,
        ], Response::HTTP_OK);
    }
}
