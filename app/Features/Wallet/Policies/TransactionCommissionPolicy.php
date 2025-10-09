<?php

namespace App\Features\Wallet\Policies;

use App\Features\Wallet\Dtos\Request\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\Request\TransactionRequestDto;

final class TransactionCommissionPolicy
{
    /**
     * To work with such values as 1.5%
     */
    private const BCSCALE = 3;

    public static function apply(TransactionRequestDto $dto): TransactionRequestCommissionedDto
    {
        $commission = bcmul(
            $dto->amount,
            bcdiv(config('wallet.commission'), 100, self::BCSCALE)
        );

        return new TransactionRequestCommissionedDto(
            $dto->idempotencyKey,
            $dto->fromUserUuid,
            $dto->toUserUuid,
            $dto->amount,
            bcadd($dto->amount, $commission),
            $commission
        );
    }
}
