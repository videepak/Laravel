<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PropertyCheckIn extends Notification
{

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $propertyName = ucwords($this->data['propertyName']) ?? '';
        $checkInDate = ucwords($this->data['checkInDate']) ?? '';
        $emoployeeName = ucwords($this->data['emoployeeName']) ?? '';
        $address = ucwords($this->data['address']) ?? '';
        $checkInTime = ucwords($this->data['checkInTime']) ?? '';

        return (new MailMessage())
            ->greeting('&nbsp;')
            ->subject(ucwords("$propertyName Property Checkin $checkInDate."))
            ->line(ucwords("$emoployeeName checked-in."))
            ->line(ucwords("Property Name: $propertyName"))
            ->line(ucwords("Address: $address."))
            ->line(ucwords("Check-In Time: $checkInTime."));
            //->cc(['akansh.pandya@galaxyweblinks.in']);
    }

    /** 
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
                //
        ];
    }
}
