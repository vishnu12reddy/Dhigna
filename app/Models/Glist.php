<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Guest;

class Glist extends Model
{
    protected $guarded = ['id'];  
    
    /**
     *  create Glist
     */

    public function create_glist($params = [], $glist_id = null)
    {
        return Glist::updateOrCreate(
            ['id' => $glist_id],
            $params
        );
        
    }

    /**
     *  get Glist
     */

    public function get_glist($params = [])
    {
        return Glist::where($params)->get();
    }

    /**
     *  get glist with pagination
     */

    public function pagination_glists($params = [])
    {
        return Glist::with('guests')->where($params)->paginate(10);
        
    }

    /**
     * The guests that belong to the glists.
     */
    public function guests()
    {
        return $this->belongsToMany(Guest::class);
    }

    /**
     *  delete glist
     */

    public  function delete_glist($params = [])
    {
        return Glist::where($params)->delete();
    }

}
