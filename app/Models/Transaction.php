<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'value',
        'currency',
        'description',
        'status',
        'completed_at',
        'failed_at',
        'transaction_id',
        'transaction_type',
        'transaction_method',
        'transaction_status',
        'transaction_reference'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    protected $attributes = [
        'currency' => 'BRL',
        'status' => 'pending',
        'transaction_type' => 'transfer',
        'transaction_method' => 'bank_transfer',
        'transaction_status' => 'pending',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
