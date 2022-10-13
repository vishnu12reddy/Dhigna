<?php

namespace App\Http\Controllers\Eventmie\Auth;

use Classiebit\Eventmie\Http\Controllers\Auth\RegisterController as BaseRegisterController;
use Classiebit\Eventmie\Models\User;
use Illuminate\Support\Facades\Hash;
use Config;
use Newsletter;


class RegisterController extends BaseRegisterController
{
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user   = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'password'  => Hash::make($data['password']),
                    'role_id'  => 2,
                ]);

        // Send welcome email
        if(!empty($user->id))
        {
            // ====================== Notification ====================== 
            $mail['mail_subject']   = __('eventmie-pro::em.register_success');
            $mail['mail_message']   = __('eventmie-pro::em.get_tickets');
            $mail['action_title']   = __('eventmie-pro::em.login');
            $mail['action_url']     = eventmie_url();
            $mail['n_type']         = "user";

            /* CUSTOM */
            $mail['user']           = $user;
            /* CUSTOM */

            // notification for
            $notification_ids       = [
                1, // admin
                $user->id, // new registered user
            ];

            //CUSTOM
            $this->mailchimp_notification($user);
            //CUSTOM

            $users = User::whereIn('id', $notification_ids)->get();
            if(checkMailCreds()) 
            {
                try {
                    // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail));
                    //CUSTOM
                    // \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'register')->delay(now()->addSeconds(10));
                    //CUSTOM
                } catch (\Throwable $th) {}
            }
            // ====================== Notification ======================     
        }
        
        $this->redirectTo = \Session::get('url.intended'); 
        
        return $user;
    }

    /**
     * 
     */
    public function mailchimp_notification($user = null)
    {
        // mailchimp subcribe new user to admin
        $admin_id   = 1;
        $admin_user = User::find($admin_id)->toArray();
        if(!empty($admin_user) && !empty($admin_user['mailchimp_apikey']) && !empty($admin_user['mailchimp_list_id']))
        {
            Config::set('newsletter.apiKey', $admin_user['mailchimp_apikey']);
            Config::set('newsletter.lists.subscribers.id', $admin_user['mailchimp_list_id']); 
           
            if(!Newsletter::isSubscribed($user->email) ) {
                
                Newsletter::subscribe($user->email);
            }
            //add tag
            Newsletter::addTags(['Register'], $user->email);    
        }
       
    }
}
