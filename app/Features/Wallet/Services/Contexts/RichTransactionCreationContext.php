<?php

namespace App\Features\Wallet\Services\Contexts;

use Carbon\Carbon;

class RichTransactionCreationContext extends TransactionCreationContext
{
    public function __construct(
        public readonly string $transactionUuid,
        public readonly string $transactionAmount,
        public readonly Carbon $transactionCreatedAt,
        public readonly string $senderUuid,
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $senderBalance,
        public readonly string $receiverUuid,
        public readonly string $receiverName,
        public readonly string $receiverEmail,
        public readonly string $receiverBalance
    )
    {
    }
}
