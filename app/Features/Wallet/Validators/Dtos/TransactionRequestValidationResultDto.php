<?php

namespace App\Features\Wallet\Validators\Dtos;

abstract class TransactionRequestValidationResultDto
{
    abstract public function isSuccessful(): bool;

    public function getErrors(): array
    {
        return [];
    }
}
