<?php

namespace App\Features\Wallet\Services\Contexts;

use Carbon\Carbon;

class TransactionCreationContext
{
    public function __construct(
        public readonly string $transactionUuid,
        public readonly string $transactionAmount,
        public readonly string $transactionCounterpartAmount,
        public readonly Carbon $transactionCreatedAt,
        public readonly string $senderUuid,
        public readonly string $receiverUuid,
    )
    {
    }
}
