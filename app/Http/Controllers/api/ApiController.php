<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\EventCollection;
use App\Http\Resources\VenueCollection;
use App\Models\Event;
use App\Service\ApiService;
use Classiebit\Eventmie\Models\Category;
use Classiebit\Eventmie\Models\Venue;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    public function filterEvents(Request $request, ApiService $service)
    {

        if ($request->hasAny('category_id', 'venue')) {
            $filteredEvents = $service->filterEvents($request);
            return response()->json(['data' => $filteredEvents, 200]);
        }

        $events = Event::all();
        return response()->json(['data' => EventCollection::make($events), 200]);
    }

    public function getCategoryList()
    {
        $category = Category::where(['status' => 1])->get();

        return response()->json(['data' => CategoryCollection::make($category)], 200);
    }
    public function getVenueList()
    {
        $venue = Venue::where(['status' => 1])->get();

        return response()->json(['data' => VenueCollection::make($venue)], 200);
    }
}
