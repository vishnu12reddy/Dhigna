<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\ContactController as BaseContactController;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends BaseContactController
{
    // contact save
    public function store_contact(Request $request)
    {
        $request->validate([
            'name'           => 'required|min:5|max:256',
            'email'          => 'required|email',
            'title'          => 'required|min:3|max:256',
            'message'        => 'required|min:2|max:1000',
        ]);

        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'title'         => $request->title,
            'message'       => $request->message,
            'created_at'    => \Carbon\Carbon::now(),
            'updated_at'    => \Carbon\Carbon::now(),
        ];
        
        $contact     = $this->contact->store_contact($data);
        
        if(empty($contact))
        {
            return redirect()->back()->with('msg', __('eventmie-pro::em.message_sent_fail')); 
        }
        
        // ====================== Notification ====================== 
        //send notification after bookings
        $msg[]                  = __('eventmie-pro::em.name').' - '.$contact->name;
        $msg[]                  = __('eventmie-pro::em.email').' - '.$contact->email;
        $msg[]                  = __('eventmie-pro::em.title').' - '.$contact->title;
        $msg[]                  = __('eventmie-pro::em.message').' - '.$contact->message;
        $extra_lines            = $msg;

        $mail['mail_subject']   = __('eventmie-pro::em.message_sent');
        $mail['mail_message']   = __('eventmie-pro::em.get_tickets');
        $mail['action_title']   = __('eventmie-pro::em.view').' '.__('eventmie-pro::em.all').' '.__('eventmie-pro::em.events');
        $mail['action_url']     = route('eventmie.events_index');
        $mail['n_type']         = "contact";

        /* CUSTOM */
        $mail['contact']        = $contact;
        /* CUSTOM */
        
        // notification for
        $notification_ids       = [
            User::whereId(1)->first()->email, // admin
            $contact->email, // contacted by
        ];
        
        // $users = User::whereIn('id', $notification_ids)->get();
        $users = $notification_ids;

        try {
            // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail, $extra_lines));
            //CUSTOM
            \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'contact')->delay(now()->addSeconds(10));
            // test
            // return view('email_templates.contact', compact('mail'));
            //CUSTOM
        } catch (\Throwable $th) {}
        // ====================== Notification ====================== 
        
        return redirect()->back()->with('msg', __('eventmie-pro::em.message_sent')); 
    }
}
