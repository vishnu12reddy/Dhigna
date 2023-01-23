<?php

namespace App\Service;

use App\Http\Resources\Event as EventResource;
use App\Http\Resources\EventCollection;
use App\Models\Event;

class ApiService
{
    public function filterEvents($request)
    {
        $query = Event::query();

        if (!is_null($request['category_id'])) {
            $query->where('category_id', $request['category_id']);
        }

        if (!is_null($request['venue'])) {
            $query->where('venue', $request['venue']);
        }

        return EventCollection::make($query->get());
    }
}
