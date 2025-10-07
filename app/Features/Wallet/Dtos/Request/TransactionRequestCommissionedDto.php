<?php

namespace App\Features\Wallet\Dtos\Request;

final class TransactionRequestCommissionedDto extends TransactionRequestDto
{
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $fromUserUuid,
        public readonly string $toUserUuid,
        public readonly string $amount,
        public readonly string $commissionedAmount,
        public readonly string $commission,
    )
    {
        parent::__construct(
            $idempotencyKey,
            $fromUserUuid,
            $toUserUuid,
            $amount
        );
    }
}
