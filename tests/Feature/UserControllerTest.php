<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_validacao_cadastro_cliente_sem_dados(): void
    {

        $response = $this->postJson(route('users.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'name',
            'type',
            'cpf_cnpj',
            'email',
            'password',
        ]);
    }

    public function test_valida_se_cpf_e_valido(): void
    {
        $dados = [
            'name' => $this->faker->name(),
            'type' => 'customer',
            'cpf_cnpj' => '12345678900',
            'email' => $this->faker->email(),
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->postJson(route('users.store'), $dados);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'cpf_cnpj'
        ]);
        $response->assertJson([
            'message' => 'O campo cpf cnpj não é um CPF ou CNPJ válido.'
        ]);
    }

    public function test_valida_se_ja_tem_email_e_cpf_cadastrado(): void
    {
        $user = User::factory()->create();
        $dados = [
            'name' => $this->faker->name(),
            'type' => 'customer',
            'cpf_cnpj' => $user->cpf_cnpj,
            'email' => $user->email,
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $this->postJson(route('users.store'), $dados);

        $response = $this->postJson(route('users.store'), $dados);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'cpf_cnpj',
            'email'
        ]);
        $response->assertJson([
            'errors' => [
                'cpf_cnpj' => [
                    'O campo cpf cnpj já está sendo utilizado.'
                ],
                'email' => [
                    'O campo email já está sendo utilizado.'
                ]
            ]
        ]);
    }
}
