<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::with('user')->get();
        return response()->json($wallets);
    }


    public function store(StoreWalletRequest $request)
    {
        $dados = $request->validated();
        $dados['balance'] = 0;
        $wallet = Wallet::create($dados);
        return response()->json([
            'message' => 'Carteira criada com sucesso',
            'data' => $wallet
        ]);
    }

    public function show(Wallet $wallet)
    {
        return response()->json($wallet);
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        $dados = $request->validated();
        $wallet->update($dados);
        return response()->json([
            'message' => 'Carteira atualizada com sucesso',
            'data' => $wallet
        ]);
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return response()->json([
            'message' => 'Carteira deletada com sucesso',
        ]);
    }
}