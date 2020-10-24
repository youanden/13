<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendClientEventCreatedNotification extends Notification
{
    use Queueable;


    protected $placeName = null;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($place)
    {
        $this->placeName = $place['name'];
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
        return (new MailMessage)
                    ->subject($this->placeName ? $this->placeName . ' has scheduled a food drive in your pickup area!' : 'A Food Drive in your pickup area has been scheduled!')
                    ->line('An agency in your pickup area has scheduled a Food Drive!')
                    ->action('See Pickup Instructions', url('/'))
                    ->line('Thank you for using Food Help Network! If you want to get involved and help us with our mission, click below:')
                    ->line('Get Involved: ' .  url('https://feedingsouthflorida.org/'));
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toSMS($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
