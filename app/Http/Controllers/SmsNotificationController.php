<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\View;

class SmsNotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // language change
        $this->middleware('common');
        $this->middleware('auth');
    }

    /**
     * sms notification
     */
    public function smsNotification($to = null, $booking = [], $invoice = null)
    {
        
        if($to->isEmpty() || empty(setting('apps.twilio_sid')) || empty(setting('apps.twilio_auth_token'))
            || empty(setting('apps.twilio_number')))
            return true;

        $to = $to->all();
        
        $files = [];
        foreach($booking as $key => $value)
            $files[$key]      = asset('storage/ticketpdfs/'.$value->customer_id.'/'.$value->id.'-'.$value->order_number.'.pdf');
        
        $files[count($files)] = asset('/storage/invoices/'.$booking[key($booking)]['customer_id'].'/'.$booking[key($booking)]['common_order'].'-invoice.pdf');
        
        $data = '';
        foreach($files as $file)
        {
            $file = urlencode($file);
            $data .=  $file."\n\n";
        }


        $msg            = setting('site.site_name')."\n";
        $msg            .= __('eventmie-pro::em.hi').' '.explode(" ", $booking[0]['customer_name'])[0]."\n";
        $msg            .= __('eventmie-pro::em.click_view_tickets')."\n";
        $msg            .= $data."\n\n";
        $msg            .= route('eventmie.mybookings_index');
        
        $account_sid   = setting('apps.twilio_sid');
        $auth_token    = setting('apps.twilio_auth_token');
        $twilio_number = setting('apps.twilio_number');
        $client        = new Client($account_sid, $auth_token);
        
        foreach($to as $key => $recipient)
        {
            try {
                $m[$key] = $client->messages->create($recipient, ['from' => $twilio_number, 'body' => $msg]);
            } catch (\Throwable $th) {
                
                return true;
            }
        }

        return true;
    }
}
