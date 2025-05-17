<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Notifications\SendTransactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTransactionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SendTransactionNotification $notification,
        public Transaction $transaction
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $this->transaction->payer->notify($this->notification);
            $this->transaction->payee->notify($this->notification);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar notificação: ' . $e->getMessage());
        }
    }
}