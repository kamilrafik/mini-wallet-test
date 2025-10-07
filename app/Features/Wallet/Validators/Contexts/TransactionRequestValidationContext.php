<?php

namespace App\Features\Wallet\Validators\Contexts;

final class TransactionRequestValidationContext
{
    public function __construct(
        public readonly int $senderId,
        public readonly int $senderBalanceId,
        public readonly string $senderBalanceAmount,
        public readonly int $receiverId,
        public readonly int $receiverBalanceId,
    )
    {
    }
}
