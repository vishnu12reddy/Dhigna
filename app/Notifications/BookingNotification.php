<?php

namespace App\Notifications;
use Classiebit\Eventmie\Notifications\BookingNotification as BaseBookingNotification;


/* CUSTOM */

use Classiebit\Eventmie\Notifications\CustomDb;
/* CUSTOM */

class BookingNotification extends BaseBookingNotification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //CUSTOM
        return [CustomDb::class];
        //CUSTOM
    }

}
