<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payee_id')->constrained('users')->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->string('currency')->default('BRL');
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('transaction_id')->unique()->nullable();
            $table->string('transaction_type')->default('transfer');
            $table->string('transaction_method')->default('bank_transfer');
            $table->string('transaction_status')->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
