<?php

namespace App\Observers;

use App\Jobs\FailSendTransactionJob;
use App\Jobs\SendTransactionJob;
use App\Models\Transaction;
use App\Notifications\SendTransactionNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        try {
            $response = Http::post('https://util.devi.tools/api/v1/notify');

            if ($response->status() === 204) {
                SendTransactionJob::dispatch(
                    new SendTransactionNotification('Notificação enviada com sucesso.', 'success'),
                    $transaction
                );
            } elseif ($response->status() === 504) {
                $data = $response->json();
                $message = $data['message'] ?? 'The service is not available, try again later';

                FailSendTransactionJob::dispatch($message);
            } else {
                Log::warning('Resposta inesperada do serviço de notificação', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao tentar enviar notificação: ' . $e->getMessage());
            FailSendTransactionJob::dispatch('Exceção: ' . $e->getMessage());
        }
    }
}