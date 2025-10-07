<?php

namespace App\Features\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'idempotency_key',
        'from_balance_id',
        'to_balance_id',
        'amount',
        'commission',
    ];
}
