<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'invoice_type',
        'status',
        'amount_atomic',
        'currency',
        'address',
        'payment_reference',
        'expires_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
