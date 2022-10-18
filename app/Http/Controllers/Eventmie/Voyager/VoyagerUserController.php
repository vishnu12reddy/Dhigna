<?php

namespace App\Http\Controllers\Eventmie\Voyager;

use Classiebit\Eventmie\Http\Controllers\Voyager\VoyagerUserController as BaseVoyagerUserController;
use App\Models\User;


class VoyagerUserController extends BaseVoyagerUserController
{
    
    protected function registrationNotification($user)
    {
        // send signup notification
        // ====================== Notification ====================== 
        $mail['mail_subject']   = __('eventmie-pro::em.register_success');
        $mail['mail_message']   = __('eventmie-pro::em.get_tickets');
        $mail['action_title']   = __('eventmie-pro::em.login');
        $mail['action_url']     = eventmie_url();
        $mail['n_type']         = "user";

        // notification for
        $notification_ids       = [
            1, // admin
            $user->id, // new registered user
        ];
        
        $users = User::whereIn('id', $notification_ids)->get();
     
        if(checkMailCreds()) 
            {
                try {
                    // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail));
                    //CUSTOM
                    \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'register')->delay(now()->addSeconds(10));
                    //CUSTOM
                } catch (\Throwable $th) {}
            }
        // ====================== Notification ======================     
    }
    
    protected function approvedOrganiserNotification($user)
    {
        // ====================== Notification ====================== 
        
        // Became Organizer successfully email
        $msg[]                  = __('eventmie-pro::em.name').' - '.$user->name;
        $msg[]                  = __('eventmie-pro::em.email').' - '.$user->email;
        $extra_lines            = $msg;

        $mail['mail_subject']   = __('eventmie-pro::em.became_organiser_successful');
        $mail['mail_message']   = __('eventmie-pro::em.became_organiser_successful_msg');
        $mail['action_title']   = __('eventmie-pro::em.view').' '.__('eventmie-pro::em.profile');
        $mail['action_url']     = route('eventmie.profile');
        $mail['n_type']         = "Approved-Organizer";
        
        
        /* CUSTOM */
        $mail['user'] = $user;
        /* CUSTOM */

        // notification for
        $notification_ids       = [
            1, // admin
            $user->id, // the organizer
        ];
        
        $users = User::whereIn('id', $notification_ids)->get();
        
        if(checkMailCreds()) 
            {
                try {
                    // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail));
                    //CUSTOM
                    \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'become_organizer')->delay(now()->addSeconds(10));
                    //CUSTOM
                } catch (\Throwable $th) {
                    
                }
            }
        // ====================== Notification ====================== 

    }
}
