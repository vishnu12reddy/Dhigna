<?php

namespace App\Http\Controllers\Eventmie;
use Classiebit\Eventmie\Http\Controllers\SendEmailController as BaseSendEmailController;

use Illuminate\Http\Request;

use Classiebit\Eventmie\Models\User;


/* CUSTOM */
use App\Notifications\BookingNotification;
use Config;
use Spatie\Newsletter\NewsletterFacade as Newsletter;
use Throwable;
use App\Http\Controllers\SmsNotificationController;

use App\Http\Controllers\InvoicesController;
use App\Mail\BookingMail;
use Mail;
use App\Service\TicketPdfGenerator;
use Classiebit\Eventmie\Models\Transaction;
use Illuminate\Support\Facades\View;

/* CUSTOM */

class SendEmailController extends BaseSendEmailController
{
    /**
     *  send email after successful bookings
     */
    public function send_email(Request $request)
    {
        $booking_data = session('booking_email_data');

         /* CUSTOM */
       
        // mailchimp notificaton
        $this->mailchimp_notification($booking_data);
        /* CUSTOM */

        if(empty($booking_data))
            return response()
                  ->json(['status' => 0]);

        // get online event info
        $event                  = $this->event->get_event(NULL, $booking_data[key($booking_data)]['event_id']);
        $mail['is_online']      = FALSE;
        if(!empty($event->online_location))
            $mail['is_online']  = TRUE;

        
        if(!is_null($booking_data[0]['transaction_id']) && $booking_data[0]['is_paid'] == 0)
        {
            $transaction = Transaction::find($booking_data[0]['transaction_id']);
        }
        
        
        // ====================== Notification ====================== 
        //send notification after bookings
        $mail['mail_data']      = $booking_data;
        $mail['transaction']    = $transaction;
        $mail['event']          = $event;
        $mail['action_title']   = __('eventmie-pro::em.download_tickets');
        $mail['action_url']     = route('eventmie.mybookings_index');
        $mail['mail_subject']   = __('eventmie-pro::em.booking_success');
        $mail['n_type']         = "bookings";

        
        $mail['event_start_date']    = userTimezone($mail['mail_data'][0]['event_start_date'].' '.$mail['mail_data'][0]['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true))  . ' - ' . userTimezone($mail['mail_data'][0]['event_end_date'].' '.$mail['mail_data'][0]['event_end_time'], 'Y-m-d H:i:s', format_carbon_date(true)) .showTimezone();
            
        $mail['event_end_date']    = userTimezone($mail['mail_data'][0]['event_start_date'].' '.$mail['mail_data'][0]['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(false)) .' -'.userTimezone($mail['mail_data'][0]['event_end_date'].' '.$mail['mail_data'][0]['event_end_time'], 'Y-m-d H:i:s', format_carbon_date(false)).showTimezone();

        $mail['tickets'] =  View::make('vendor.eventmie-pro.mail.custom.tickets', ['mail_data' => (object)$mail])->render();
        //CUSTOM
        
        $invoice          = new InvoicesController($booking_data);
        $mail['invoices']  = $invoice->makeInvoice();

       
        //CUSTOM
        $notification_ids       = [1, $booking_data[key($booking_data)]['organiser_id'], $booking_data[key($booking_data)]['customer_id']];
        
        $users = User::whereIn('id', $notification_ids)->get();

        try {
            //CUSTOM
            Mail::to($users)->send(new BookingMail($mail));   

            //CUSTOM
            \Notification::locale(\App::getLocale())->send($users, new BookingNotification($mail));

            // send SMS to customers only
            $notification_ids       = [$booking_data[key($booking_data)]['customer_id']];
            $users = User::whereIn('id', $notification_ids)->whereNotNull('phone')->get();
            $sms    =  new SmsNotificationController;
            $sms->smsNotification($users->pluck('phone'), $booking_data, $mail['invoices']);

        } catch (\Throwable $th) {
            // dd($th->getMessage());
        }
        // ====================== Notification ====================== 
        
        // delete booking_email_data data from session
        session()->forget(['booking_email_data']);

        return response()
                  ->json(['status' => 1]);
    }

    /* Mailchimp Notification */
    protected function mailchimp_notification($booking = [])
    {   
        // mailchimp subcribe new booking to organiser 
        $organiser_id   = $booking[key($booking)]['organiser_id'];
        $organiser_user = User::find($organiser_id)->toArray();
        
        if(!empty($organiser_user) && !empty($organiser_user['mailchimp_apikey']) && !empty($organiser_user['mailchimp_list_id']))
        {
            Config::set('newsletter.apiKey', $organiser_user['mailchimp_apikey']);
            Config::set('newsletter.lists.subscribers.id', $organiser_user['mailchimp_list_id']); 
            
            if(!Newsletter::isSubscribed($booking[key($booking)]['customer_email']) ) 
            {
                Newsletter::subscribe($booking[key($booking)]['customer_email']);
            }    
        
            //add tag
            Newsletter::addTags([$booking[key($booking)]['event_title']], $booking[key($booking)]['customer_email']);
        }

        return true;
    }
}