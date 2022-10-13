<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\ProfileController as BaseProfileController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Classiebit\Eventmie\Models\User;
use Facades\Classiebit\Eventmie\Eventmie;

use Classiebit\Eventmie\Notifications\MailNotification;
use Auth;

class ProfileController extends BaseProfileController
{
    public function index($view = 'vendor.eventmie-pro.profile.profile', $extra = [])
    {
        return parent::index($view, $extra);
    }

    // update user
    public function updateAuthUser (Request $request)
    {
        // demo mode restrictions
        if(config('voyager.demo_mode'))
        {
            return error_redirect('Demo mode');
        }
        
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.Auth::id()
        ]);
        
        if(!empty($request->current))
        {
            $data = $this->updateAuthUserPassword($request);
        
            if($data['status'] == false)
            {
                return error_redirect($data['errors']);
            }
        }
        
        $user = User::find(Auth::id());

        $user->name                  = $request->name;
        $user->email                 = $request->email;
        $user->address               = $request->address;
        $user->phone                 = $request->phone;
        
        //CUSTOM
        $user->taxpayer_number                 = $request->taxpayer_number;

        if(Auth::user()->hasRole('organiser')) {
            $user->org_description       = $request->org_description;
            $user->org_facebook          = $request->org_facebook;
            $user->org_instagram         = $request->org_instagram;
            $user->org_youtube           = $request->org_youtube;
            $user->org_twitter           = $request->org_twitter;
            $user->organisation          = $request->organisation;
            
            $this->sellerInfo($request, $user);
            $this->organizerCountry($request, $user);
        }
        $this->mailchimp($request, $user);

        $this->uploadImage($request, $user);
        //CUSOTM
        $this->updatebankDetails($request, $user);

        
        
        $user->save();

        // redirect no matter what so that it never turns back
        $msg = __('eventmie-pro::em.saved').' '.__('eventmie-pro::em.successfully');
        return success_redirect($msg, route('eventmie.profile'));
        
    }

    // update user role
    public function updateAuthUserRole(Request $request)
    {
        $this->validate($request, [
            'organisation'  => 'required',
        ]);
        
        $manually_approve_organizer = (int)setting('multi-vendor.manually_approve_organizer');
        
        
        $user = User::find(Auth::id());
        
        // manually aporove organizer setting on then don't change role
        if(empty($manually_approve_organizer))
        {
            //CUSTOM
            $user->roles()->sync([3]);
            //CUSTOM

            $user->role_id      = 3;

        } 

        $user->organisation = $request->organisation;

        $user->save();
    
        // ====================== Notification ====================== 
        // Manual Organizer approval email
        $msg[]                  = __('eventmie-pro::em.name').' - '.$user->name;
        $msg[]                  = __('eventmie-pro::em.email').' - '.$user->email;
        $extra_lines            = $msg;

        $mail['mail_subject']   = __('eventmie-pro::em.requested_to_become_organiser');
        $mail['mail_message']   = __('eventmie-pro::em.become_organiser_notification');
        $mail['action_title']   = __('eventmie-pro::em.view').' '.__('eventmie-pro::em.profile');
        $mail['action_url']     = route('eventmie.profile');
        $mail['n_type']         = "Approve-Organizer";
        if(empty($manually_approve_organizer))
        {
            // Became Organizer successfully email
            $msg[]                  = __('eventmie-pro::em.name').' - '.$user->name;
            $msg[]                  = __('eventmie-pro::em.email').' - '.$user->email;
            $extra_lines            = $msg;

            $mail['mail_subject']   = __('eventmie-pro::em.became_organiser_successful');
            $mail['mail_message']   = __('eventmie-pro::em.became_organiser_successful_msg');
            $mail['action_title']   = __('eventmie-pro::em.view').' '.__('eventmie-pro::em.profile');
            $mail['action_url']     = route('eventmie.profile');
            $mail['n_type']         = "Approved-Organizer";
        }

        /* CUSTOM */
        $mail['user'] = $user;
        /* CUSTOM */
        
        // notification for
        $notification_ids       = [
            1, // admin
            $user->id, // logged in user by
        ];
        
        $users = User::whereIn('id', $notification_ids)->get();
        try {
            // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail, $extra_lines));
            //CUSTOM
            \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'become_organizer')->delay(now()->addSeconds(10));
            // test
            // return view('email_templates.becomeOrganizer', compact('mail'));
            //CUSTOM
        } catch (\Throwable $th) {}
        // ====================== Notification ====================== 
        

        return redirect()->route('eventmie.profile');
    }

    /**
     *  custom functions start
     */

    // mailchimp fields
    protected function mailchimp(Request $request, $user = null)
    {
        $user->mailchimp_apikey      = $request->mailchimp_apikey;
        $user->mailchimp_list_id     = $request->mailchimp_list_id;
        
    }

    /**
     *  upload imgate
     */
    protected function uploadImage(Request $request, User $user)
    {
        $path   = 'users/';

        // for image
        if($request->hasfile('avatar')) 
        { 
            $request->validate([
                'avatar' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            
            ]); 
        
            $file = $request->file('avatar');
    
            $extension       = $file->getClientOriginalExtension(); // getting image extension
            $avatar          = time().rand(1,988).'.'.$extension;
            $file->storeAs('public/'.$path, $avatar);
            
            $user->avatar    = $path.$avatar;
            
        }
        
        if(empty($user->avatar) || $user->avatar == 'users/default.png')
        {
            $request->validate([
                'avatar' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            
            ]); 
        }
        if(Auth::user()->hasRole('organiser')) 
        {
            if($request->hasfile('seller_signature')) 
            { 
                $request->validate([
                    'seller_signature' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
                
                ]); 
            
                $file = $request->file('seller_signature');
        
                $extension       = $file->getClientOriginalExtension(); // getting image extension
                $avatar          = time().rand(1,988).'.'.$extension;
                $file->storeAs('public/'.$path, $avatar);
                
                $user->seller_signature    = $path.$avatar;
                
            }
            
            if(empty($user->seller_signature) || $user->seller_signature == 'users/default.png')
            {
                $request->validate([
                    'seller_signature' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
                
                ]); 
            }

        }

    }

    /**
     *  seller info 
     * */
    protected function sellerInfo(Request $request, User $user)
    {
        $request->validate([
            'seller_name'        => 'required|string|max:256',
            'seller_info'        => 'required|string|max:256',
            'seller_tax_info'    => 'required|string|max:256',
            'seller_note'        => 'required|string|max:256',
        ]);

        $user->seller_name       = $request->seller_name;
        $user->seller_info       = $request->seller_info;
        $user->seller_tax_info   = $request->seller_tax_info;
        $user->seller_note       = $request->seller_note;
    }

    /**
     *  country
     * */
    protected function organizerCountry(Request $request, User $user)
    {
        $request->validate([
            'country'        => 'required',
        ]);

        $user->country       = $request->country;
    }
}
