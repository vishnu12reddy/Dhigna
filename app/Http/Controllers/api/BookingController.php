<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventsShowResource;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    /**
     * showBooking
     *
     * @param  mixed $request
     * @return void
     */
    public function showBooking(Request $request)
    {
        $event = Event::with(['category'])->where('id', $request->id)->first();
        return  new EventResource($event);
    }

    /**
     * ticketDetail
     *
     * @param  mixed $request
     * @return void
     */
    public function ticketDetail(Request $request)
    {
        $ticketInfo = Ticket::where('event_id', $request->id)->get();
        return  TicketResource::collection($ticketInfo);
    }
}
