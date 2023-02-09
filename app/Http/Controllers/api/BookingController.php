<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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
        
        return response()->json(["message" => "success"], 200);
    }

    /**
     * ticketDetail
     *
     * @param  mixed $request
     * @return void
     */
    public function ticketDetail(Request $request)
    {
        return response()->json(["message" => "success"], 200);
    }
}
