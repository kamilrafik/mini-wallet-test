<?php

namespace App\Features\Wallet\Dtos\Response;

abstract class TransactionRequestProcessingResultDto
{
    abstract public function isSuccessful(): bool;
    abstract public function toArray(): array;
}
