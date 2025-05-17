<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTransactionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $type,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Transaction Notification')
            ->greeting('Hello!')
            ->line($this->message)
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'created_at' => now(),
            'updated_at' => now(),
            'read_at' => null,
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            'data' => [
                'message' => $this->message,
                'type' => $this->type,
            ],
            'read' => false,
        ];
    }
}