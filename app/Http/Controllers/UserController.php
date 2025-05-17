<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('wallet')->get();
        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $dados = $request->validated();
        $dados['password'] = bcrypt($dados['password']);
        $user = User::create($dados);
        return response()->json([
            'message' => 'Usuário criado com sucesso',
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $dados = $request->validated();
        if (isset($dados['password'])) {
            $dados['password'] = bcrypt($dados['password']);
        }
        $user->update($dados);
        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json([
            'message' => 'Usuário deletado com sucesso',
        ]);
    }
}