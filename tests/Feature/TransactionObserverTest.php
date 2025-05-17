<?php

namespace Tests\Feature;

use App\Jobs\FailSendTransactionJob;
use App\Jobs\SendTransactionJob;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\SendTransactionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_send_transaction_job_on_successful_notification()
    {
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response(null, 204),
        ]);

        Queue::fake();

        $payer = User::factory()->create();
        $payee = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'value' => 100,
        ]);

        Queue::assertPushed(SendTransactionJob::class, function ($job) use ($transaction) {
            return $job->transaction->id === $transaction->id &&
                $job->notification instanceof SendTransactionNotification &&
                $job->notification->type === 'success';
        });

        Queue::assertNotPushed(FailSendTransactionJob::class);
    }

    public function test_it_dispatches_fail_send_transaction_job_on_failed_notification()
    {
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response([
                'status' => 'error',
                'message' => 'The service is not available, try again later',
            ], 504),
        ]);

        Queue::fake();

        $payer = User::factory()->create();
        $payee = User::factory()->create();

        Transaction::factory()->create([
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'value' => 100,
        ]);

        Queue::assertPushed(FailSendTransactionJob::class, function ($job) {
            return $job->message === 'The service is not available, try again later';
        });

        Queue::assertNotPushed(SendTransactionJob::class);
    }
}