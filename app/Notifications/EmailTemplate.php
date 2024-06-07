<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailTemplate extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($param, $subject)
    {
        $this->checkUser = \App\UserNotification::where(
            [
                'type' => 4,
                'user_id' => \Auth::user()->id,
            ]
        )
        ->first();

        $this->param = $param;
        $this->subject = $subject;
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
        $mailMessage = new MailMessage();
        
        $mailMessage->subject($this->subject);

        if (is_null($this->checkUser) || (!is_null($this->checkUser) && $this->checkUser->email)) {
            $mailMessage->cc(
                [
                    \Auth::user()->email,
                    //'akansh.pandya@galaxyweblinks.in'
                ]
            );
        }
        
        $mailMessage
            ->markdown(
                'email.emailTemplate',
                [
                    'detail' => $this->param
                ]
            );

        return $mailMessage;

        // dd(\Auth::user()->email);
        // return (
        //     new MailMessage())
        //     ->subject($this->subject)
        //     ->cc(\Auth::user()->email)
        //     ->markdown(
        //         'email.emailTemplate',
        //         [
        //             'detail' => $this->param
        //         ]
        //     );
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
