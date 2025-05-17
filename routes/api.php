<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);
Route::apiResource('wallet', WalletController::class);
Route::post('transaction', [TransactionController::class, 'store'])->name('api.transaction.store');
Route::get('transaction', [TransactionController::class, 'index'])->name('api.transaction.index');