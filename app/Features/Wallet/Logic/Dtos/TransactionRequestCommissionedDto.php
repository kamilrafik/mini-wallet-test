<?php

namespace App\Features\Wallet\Logic\Dtos;

use App\Features\Wallet\Requests\TransactionRequestDto;

final class TransactionRequestCommissionedDto extends TransactionRequestDto
{
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $fromUserUuid,
        public readonly string $toUserUuid,
        public readonly string $amount,
        public readonly string $commissionedAmount,
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
