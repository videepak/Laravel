<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ViolationMail extends Notification
{

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {

        //Get logo and company name using helper function:Start
        $this->logo = getLogo()['logo'];
        $this->companyName = getLogo()['companyName'];
        //Get logo and company name using helper function:End
        $this->param = $data;
        $this->subject = isset($this->param[0]) ? $this->param[0] : config('constants.violationEmailSubject');
        $this->body = isset($this->param[0]) ? $this->param[1] : config('constants.violationEmailBody');
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

        $mailMessage->subject(ucwords($this->subject))
            ->greeting('Hello,')
            ->line($this->body)
            ->action('Click Here To See Violation Detail.', $notifiable->url)
            ->cc($notifiable->cc)
            ->markdown(
                'email.violationDetail',
                [
                    'url' => $notifiable->url,
                    'color' => 'green',
                    'body' => ucwords($this->param[1]),
                    'companyName' => ucwords($this->companyName),
                    'logo' => $this->logo
                ]
            );

//        if (!empty($this->pdfDoucment)) {
//
//            $mailMessage->attach($this->pdfDoucment, [
//                'as' => 'violation.pdf',
//                'mime' => 'application/pdf',
//            ]);
//        }

        return $mailMessage;
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
