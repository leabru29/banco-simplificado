<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $dados = $request->validated();

                $payer = User::findOrFail($dados['payer_id']);
                $payee = User::findOrFail($dados['payee_id']);

                $payerWallet = $payer->wallet;

                if ($payerWallet->balance < $dados['value']) {
                    return response()->json(['message' => 'Saldo insuficiente'], 422);
                }

                $getAuthorizer = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->get('https://util.devi.tools/api/v2/authorize');

                if (
                    $getAuthorizer->json()['status'] !== 'success' ||
                    $getAuthorizer->json()['data']['authorization'] !== true
                ) {
                    return response()->json(['message' => 'Transação não autorizada'], 422);
                }

                $payeeWallet = $payee->wallet;
                $value = $dados['value'];

                $payerWallet->decrement('balance', $value);
                $payeeWallet->increment('balance', $value);

                Transaction::create([
                    'payer_id' => $payer->id,
                    'payee_id' => $payee->id,
                    'value' => $value,
                    'currency' => $dados['currency'] ?? 'BRL',
                    'description' => $dados['description'] ?? null,
                ]);

                return response()->json(['message' => 'Transferência realizada com sucesso'], 201);
            });
        } catch (\Exception $e) {
            Log::error('Exceção capturada: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao processar a transação'], 500);
        }
    }
}