<?php

namespace App\Features\Wallet\Dtos\Response;

class TransactionRequestProcessingSuccessDto extends TransactionRequestProcessingResultDto
{
    public function __construct(
        private readonly string $transactionUuid,
        private readonly string $transactionAmount,
    )
    {
    }

    public function isSuccessful(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'transactionUuid' => $this->transactionUuid,
            'transactionAmount' => $this->transactionAmount,
        ];
    }
}
