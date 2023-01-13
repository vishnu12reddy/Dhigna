<?php

namespace App\Http\Resources;

use Classiebit\Eventmie\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "venue_type" => $this->venue_type,
            "address" => $this->address,
            "country" => $this->country_id,
            "state" => $this->state,
            "city" => $this->city,
            "zipcode" => $this->zipcode,
            "amenities" => $this->amenities,
            "slug" => $this->slug,
            "seated_guestnumber" => $this->seated_guestnumber,
            "standing_guestnumber" => $this->standing_guestnumber,
            "neighborhoods" => $this->neighborhoods,
            "pricing" => $this->pricing,
            "availability" => $this->availability,
            "food" => $this->food,
            "show_quoteform" => $this->show_qouteform,
            "contact_email" => $this->contact_email,
            "glat" => $this->glat,
            "glong" => $this->glong,
            "images" => $this->images,
            "organizer_id" => $this->organizer_id,
            "status" => $this->status,

        ];
    }
}
