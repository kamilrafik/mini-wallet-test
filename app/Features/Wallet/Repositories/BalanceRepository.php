<?php

namespace App\Features\Wallet\Repositories;

use App\Features\Wallet\Models\Balance;

class BalanceRepository
{
   public function findByUserId(int $userId): ?Balance
   {
       return Balance::query()->where('user_id', $userId)->first();
   }

   public function findById(int $id, $cache = false): ?Balance
   {
       return Balance::query()->where('id', $id)->first();
   }
}
