<?php

namespace App\Features\Wallet\Validators\Dtos;

use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Validators\Contexts\TransactionRequestValidationContext;

class TransactionRequestValidationSuccessDto extends TransactionRequestValidationResultDto
{
    public function __construct(
        public readonly TransactionRequestValidationContext $context,
    )
    {
    }

    public function isSuccessful(): bool
    {
        return true;
    }
}
