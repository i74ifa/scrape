<?php

namespace App\Notifications\Customer;

use App\Services\Fcm\FcmBody;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeOrderStatusNotify extends Notification
{
    use Queueable;


    /**
     * Create a new notification instance.
     */
    public function __construct(protected $order, protected $title = null, protected $description = null, protected $url = null)
    {
        $this->title = $title ?? __('Order Status Changed');
        $this->description = $description ?? __('Order Status Changed');
        $this->url = $url ?? route('orders.show', $order->id);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm'];
    }

    public function toFcm($notifiable): FcmBody
    {
        $userLocale = $notifiable->locale ?? 'ar';

        return new FcmBody([
            'title' => trans($this->title, $userLocale),
            'description' => trans($this->description, $userLocale),
            'url' => $this->url,
            'token' => $notifiable->device_token,
        ]);
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
