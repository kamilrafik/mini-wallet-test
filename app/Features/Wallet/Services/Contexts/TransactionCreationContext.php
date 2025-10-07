<?php

namespace App\Features\Wallet\Services\Contexts;

use Carbon\Carbon;

class TransactionCreationContext
{
    public function __construct(
        public readonly string $transactionUuid,
        public readonly string $transactionAmount,
        public readonly Carbon $transactionCreatedAt,
        public readonly string $senderUuid,
        public readonly string $senderBalance,
        public readonly string $receiverUuid,
        public readonly string $receiverBalance
    )
    {
    }
}
