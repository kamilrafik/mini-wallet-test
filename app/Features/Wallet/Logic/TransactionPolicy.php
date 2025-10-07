<?php

namespace App\Features\Wallet\Logic;

use App\Features\Wallet\Requests\TransactionRequestDto;

final class TransactionPolicy
{
    public static function apply(TransactionRequestDto $dto): TransactionRequestDto
    {
        return new TransactionRequestDto(
            $dto->idempotencyKey,
            $dto->fromUserUuid,
            $dto->toUserUuid,
            bcmul($dto->amount, bcadd(1, bcdiv(config('wallet.commission'), 100)))
        );
    }
}
