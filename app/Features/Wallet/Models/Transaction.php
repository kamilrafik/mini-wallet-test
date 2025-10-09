<?php

namespace App\Features\Wallet\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $idempotency_key
 * @property int $from_balance_id
 * @property int $to_balance_id
 * @property string $amount
 * @property string $commission
 * @property string $counterpart_amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'idempotency_key',
        'from_balance_id',
        'to_balance_id',
        'amount',
        'commission',
        'counterpart_amount',
    ];
}
