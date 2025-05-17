<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailSendTransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fail to send transaction notification')
            ->greeting('Hello!')
            ->line($this->message)
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'created_at' => now(),
            'updated_at' => now(),
            'read_at' => null,
            'type' => 'fail_send_transaction',
            'data' => [
                'message' => $this->message,
            ],
        ];
    }
}