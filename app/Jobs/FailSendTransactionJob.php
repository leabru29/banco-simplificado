<?php

namespace App\Jobs;

use App\Notifications\FailSendTransactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class FailSendTransactionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
    ) {}

    public function handle(): void
    {
        Notification::send(null, new FailSendTransactionNotification($this->message));
    }
}