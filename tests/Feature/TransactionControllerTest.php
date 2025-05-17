<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_enviar_dinheiro_para_logista(): void
    {
        Queue::fake();
        $user = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        $shopkeeper = User::factory()->create([
            'type' => 'shopkeeper',
            'cpf_cnpj' => fake()->unique()->cnpj(),
        ]);

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);
        Wallet::factory()->create([
            'user_id' => $shopkeeper->id,
            'balance' => 0,
        ]);

        $dados = [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $shopkeeper->id,
        ];

        $this->mockAutorizadorComSucesso();

        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Transferência realizada com sucesso',
        ]);
        $this->assertDatabaseHas('transactions', [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $shopkeeper->id,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 900,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $shopkeeper->id,
            'balance' => 100,
        ]);
    }

    public function test_usuario_pode_enviar_dinheiro_para_outro_usuario(): void
    {
        Queue::fake();
        $user = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        $recipient = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);
        Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 0,
        ]);

        $dados = [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $recipient->id,
        ];

        $this->mockAutorizadorComSucesso();
        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Transferência realizada com sucesso',
        ]);
        $this->assertDatabaseHas('transactions', [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $recipient->id,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 900,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $recipient->id,
            'balance' => 100,
        ]);
    }

    public function test_logista_nao_pode_enviar_dinheiro_para_outro_usuario(): void
    {
        $shopkeeper = User::factory()->create([
            'type' => 'shopkeeper',
            'cpf_cnpj' => fake()->unique()->cnpj(),
        ]);

        $recipient = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        Wallet::factory()->create([
            'user_id' => $shopkeeper->id,
            'balance' => 1000,
        ]);
        Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 0,
        ]);

        $dados = [
            'value' => 100,
            'payer_id' => $shopkeeper->id,
            'payee_id' => $recipient->id,
        ];

        $this->mockAutorizadorComSucesso();
        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['payer']);
        $response->assertJson([
            'message' => 'Logistas não podem enviar dinheiro.',
            'errors' => [
                'payer' => ['Logistas não podem enviar dinheiro.'],
            ],
        ]);
    }

    public function test_usuario_nao_pode_enviar_dinheiro_para_outro_usuario_sem_saldo(): void
    {
        $user = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        $recipient = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 0,
        ]);

        $dados = [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $recipient->id,
        ];

        $this->mockAutorizadorComSucesso();
        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertUnprocessable();
        $response->assertJson(['message' => 'Saldo insuficiente']);
    }

    public function test_transacao_nao_autorizada(): void
    {
        $user = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        $recipient = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->unique()->cpf(),
        ]);

        Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);

        Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 0,
        ]);

        // Mockando o autorizador com resposta de falha
        $this->mockAutorizadorComErro();

        $dados = [
            'value' => 100,
            'payer_id' => $user->id,
            'payee_id' => $recipient->id,
        ];

        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertUnprocessable();
        $response->assertJson([
            'message' => 'Transação não autorizada',
        ]);
    }

    public function test_rollback_em_caso_de_erro_na_transacao(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response([
                'status' => 'success',
                'data' => ['authorization' => true],
            ], 200),
        ]);

        $payer = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->cpf(),
        ]);
        $payee = User::factory()->create([
            'type' => 'customer',
            'cpf_cnpj' => fake()->cpf(),
        ]);

        Wallet::factory()->create([
            'user_id' => $payer->id,
            'balance' => 1000,
        ]);
        Wallet::factory()->create([
            'user_id' => $payee->id,
            'balance' => 0,
        ]);

        Transaction::saving(function () {
            throw new \Exception('Erro simulado');
        });

        $dados = [
            'value' => 100,
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ];

        $response = $this->postJson(route('api.transaction.store'), $dados);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Erro ao processar a transação',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payer->id,
            'balance' => 1000,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $payee->id,
            'balance' => 0,
        ]);

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'value' => 100,
        ]);
    }

    protected function mockAutorizadorComSucesso(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response([
                'status' => 'success',
                'data' => ['authorization' => true],
            ], 200),
        ]);
    }

    protected function mockAutorizadorComErro(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response([
                'status' => 'fail',
                'data' => [
                    'authorization' => false
                ],
            ], 200),
        ]);
    }
}