<?php

namespace App\Http\Controllers\Eventmie;

use Classiebit\Eventmie\Http\Controllers\PagesController as BasePagesController;

use Facades\Classiebit\Eventmie\Eventmie;
use Classiebit\Eventmie\Models\Page;
use Classiebit\Eventmie\Models\Event;

class PagesController extends BasePagesController
{
    /* CUSTOM */
    /**
     * Show page and event via short_url 
     */
    public function view($page = null, $view = 'eventmie::pages', $extra = [])
    {
        // First find event via Short_url
        $event  = Event::where(['short_url' => $page])->first();
        if($event) {
            // redirect to event page
            return redirect(route('eventmie.events_show', [$event->slug]));
        }

        $page   = Page::where(['slug' => $page])->firstOrFail();
        return Eventmie::view($view, compact('page', 'extra'));
    }
}
