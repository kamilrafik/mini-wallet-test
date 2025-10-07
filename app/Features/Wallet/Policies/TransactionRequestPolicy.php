<?php

namespace App\Features\Wallet\Policies;

use App\Features\Wallet\Dtos\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\TransactionRequestDto;

final class TransactionRequestPolicy
{
    public static function apply(TransactionRequestDto $dto, $amountPrecision): TransactionRequestCommissionedDto
    {
        $amount = bcround($dto->amount, $amountPrecision);

        return new TransactionRequestCommissionedDto(
            $dto->idempotencyKey,
            $dto->fromUserUuid,
            $dto->toUserUuid,
            $amount,
            bcmul($amount, bcadd(1, bcdiv(config('wallet.commission'), 100)))
        );
    }
}
