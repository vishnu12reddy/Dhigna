<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Review;
use Auth;

class ReviewsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'  => 'gt:0',
            'event_id' => 'gt:0',
            'review'   => 'string|max:5000|nullable',
            'rating'   => 'numeric|nullable',
        ]);

        $params = [
            'user_id'  => $request->user_id,
            'event_id' => $request->event_id,
            'rating'   => $request->rating,
            'review'   => $request->review,
        ];

        $review = Review::where(['user_id' =>  $params['user_id'], 'event_id' => $params['event_id'] ])->first();

        if(empty($request->rating))
            $params['rating'] = empty($review) ? 0 : $review->rating;
        
        if(empty($request->review))
            $params['review'] = empty($review) ? null : $review->review;
        
        if(empty($params['rating']) && empty($params['review']))
        {
            $msg = __('eventmie-pro::em.faild_review');
            
            $err_response[] = $msg;

            return redirect()->back()->withErrors($err_response);
        }

        $review = Review::updateOrCreate(
            ['user_id' =>  $params['user_id'], 'event_id' => $params['event_id'] ],
            $params
        );

        $msg = __('eventmie-pro::em.save_review');
        
        session()->flash('status', $msg);

        // ====================== Notification ====================== 
        $mail['mail_subject']   = __('eventmie-pro::em.thank_you_rating');
        $mail['mail_message']   = __('eventmie-pro::em.review');
        $mail['action_title']   = __('eventmie-pro::em.review');
        $mail['action_url']     = eventmie_url();
        $mail['n_type']         = "review";

        $event = Event::where(['id' => $request->event_id])->first();
        $organizer_id = $event->user_id;

        /* CUSTOM */
        $mail['review'] = $review;
        $mail['user']   = Auth::user();
        $mail['event']  = $event;
        /* CUSTOM */

        // notification for
        $notification_ids       = [
            $organizer_id,
        ];

        $users = User::whereIn('id', $notification_ids)->get();

        if(checkMailCreds()) 
        {
            try {
                // \Notification::locale(\App::getLocale())->send($users, new MailNotification($mail));
                //CUSTOM
                \App\Jobs\RegistrationEmailJob::dispatch($mail, $users, 'review')->delay(now()->addSeconds(10));
                // test
                // return view('email_templates.review', compact('mail'));
                //CUSTOM
            } catch (\Throwable $th) {}
        }
        // ====================== Notification ======================    

        return redirect()->back()->with('status', $msg);
    }
}
