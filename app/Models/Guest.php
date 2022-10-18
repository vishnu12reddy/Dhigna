<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Glist;

class Guest extends Model
{
    protected $guarded = ['id'];  


    /**
     *  get guests 
     */

    public function get_guests($params = [])
    {
        $glist = Glist::where($params)->first();
        
        return $glist->guests()->paginate(10);
        
        
    }

    /**
     *  add guest
     */

    public function add_guest($params = [], $guest_id = null)
    {
        // if have no guest id then create new event
        return Guest::updateOrCreate(
            ['id' => $guest_id],
            $params
        );
    }

        /**
     * The glists that belong to the user.
     */
    public function glists()
    {
        return $this->belongsToMany(Glist::class);
    }

        /**
     * The guests that belong to the glists.
     */
    public function guests()
    {
        return $this->belongsToMany(Guest::class);
    }

}
