<?php

namespace App\Features\Wallet\Policies;

use App\Features\Wallet\Dtos\Request\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\Request\TransactionRequestDto;
use App\Features\Wallet\Support\BcMath;

final class TransactionCommissionPolicy
{
    public static function apply(TransactionRequestDto $dto): TransactionRequestCommissionedDto
    {
        $bcMath = new BcMath();

        $commission = $bcMath->mul(
            $dto->amount,
            $bcMath->normalizePercentage(config('wallet.commission'))
        );

        return new TransactionRequestCommissionedDto(
            $dto->idempotencyKey,
            $dto->fromUserUuid,
            $dto->toUserUuid,
            $dto->amount,
            $bcMath->add($dto->amount, $commission),
            $commission
        );
    }
}
