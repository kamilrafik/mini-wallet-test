<?php

namespace App\Features\Wallet\Logic;

use App\Features\Wallet\Logic\Dtos\TransactionRequestCommissionedDto;
use App\Features\Wallet\Requests\TransactionRequestDto;

final class TransactionPolicy
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
