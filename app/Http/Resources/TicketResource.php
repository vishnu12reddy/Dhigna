<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            "ticket_title" => $this->title,
            "price" => $this->price,
            "quantity" => $this->quantity,
            "description" => $this->description,
            "event_id" => $this->event_id,
            "customer_limit" => $this->customer_limit,
            "ticket_soldout" => $this->t_soldout,
            "sale_start_date" => $this->sale_start_date,
            "sale_end_date" => $this->sale_end_date,
            "sale_price" => $this->sale_price,
            "is_donation" => $this->is_donation,
        ];
    }
}
