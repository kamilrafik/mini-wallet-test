<?php

namespace App\Features\Wallet\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $balance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Balance extends Model
{
    protected $fillable = [];
}
