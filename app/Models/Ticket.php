<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Classiebit\Eventmie\Models\Ticket as BaseModel;
use App\Models\Seatchart;
use App\Models\Attendee;
use App\Models\Promocode;


class Ticket extends BaseModel
{
    use HasFactory;

    public function get_event_tickets($params = [])
    {
        if (!empty($params['ticket_ids'])) {
            $result = Ticket::with([
                'taxes',
                'seatchart',
                'attendees',
                'attendees.booking',
                'promocodes',
                'seatchart.seats'  => function ($query) {
                    // $query->where(['status' => 1]);
                },
            ])->whereIn('id', $params['ticket_ids'])
                ->where('event_id', $params['event_id'])
                ->orderBy('price')
                ->get();
        } else {
            $result = Ticket::with([
                'taxes',
                'seatchart',
                'attendees',
                'attendees.booking',
                'promocodes',
                'seatchart.seats'  => function ($query) {
                    // $query->where(['status' => 1]);
                },
            ])->where(['event_id' => $params['event_id']])
                ->orderBy('price')
                ->get();
        }

        return $result;
    }

    /**
     * Get the seatchart record associated with the ticket.
     */
    public function seatchart()
    {
        return $this->hasOne(Seatchart::class);
    }


    /**
     * Get the attendees record associated with the ticket.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get the promocodes record associated with the ticket.
     */
    public function promocodes()
    {
        return $this->belongsToMany(Promocode::class, 'ticket_promocode');
    }

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
