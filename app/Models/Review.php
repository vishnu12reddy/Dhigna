<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\User;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the event that owns the review.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    
    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
