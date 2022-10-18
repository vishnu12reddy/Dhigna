<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Event;
use App\Models\User;
use App\Models\Review;
use Auth;

class ManageReviewsController extends Controller
{

    public $organizer_id = null;
    public $is_admin     = false;

    public function __construct(Request $request)
    {
        $this->middleware(['review', 'common']);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->is_admin($request);

        $events = [];
        
        if(Auth::user()->hasRole('admin'))
        {
            $events = Event::with(['user'])->get();
        }
        else
        {
            $events = Event::with(['user'])->where(['user_id' => $this->organizer_id])->get();
            
        }

        $is_admin  = $this->is_admin;

        if($events->isEmpty())
        {
            abort(404);
        }

        if($request->wantsJson())
        {
            $this->is_admin($request);
            
            $request->validate([
                'event_id'     => 'gt:0|required',
            ]);

            $events = Event::where(['user_id' => $this->organizer_id])->get();

            if($events->isEmpty())
            {
                return response()->json(['error' => __('eventmie-pro::em.reviews').' '.__('eventmie-pro::em.not_found'), 'status' => false ]);
            }

            $event = Event::with(['reviews', 'reviews.event'])->where(['id' => $request->event_id, 'user_id' => $this->organizer_id])->first();
            
            if(empty($event))
            {
                return response()->json(['error' => __('eventmie-pro::em.reviews').' '.__('eventmie-pro::em.not_found'), 'status' => false]);
            }

            $reviews = $event->reviews()->with(['event'])->orderBy('created_at', 'desc')->paginate(10);
            

            return response([
                'status'  => true,
                'manage_reviews' => $reviews->jsonSerialize(),
            ], Response::HTTP_OK);
    
        }
        else
        {
            return view('manage_reviews.index', compact('events', 'is_admin'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->is_admin($request);

        $request->validate([
            'event_id'   => 'gt:0|required',
            'review_id'  => 'gt:0|required',
        ]);

        Event::where(['user_id' => $this->organizer_id,  'id' => $request->event_id])->firstOrFail();

        $review = Review::where(['event_id' => $request->event_id, 'id' => $request->review_id])->firstOrFail();
        
        $review->status = !$review->status;

        $review->save();
        
        return response()->json(['status' => true]);
    }

    protected function is_admin(Request $request)
    {

        if(Auth::user()->hasRole('admin'))
        {
            
            $this->is_admin = true;

            if($request->wantsJson())
            {
                $request->validate([
                    'organizer_id' => 'gt:0|required',
                ]);

                $this->organizer_id = $request->organizer_id;
            }

        }
        else
        {
                    
            $this->organizer_id = Auth::id();

        }
    
    }
}
