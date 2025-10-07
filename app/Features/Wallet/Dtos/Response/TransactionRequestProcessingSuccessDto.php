<?php

namespace App\Features\Wallet\Dtos\Response;

use Carbon\Carbon;

class TransactionRequestProcessingSuccessDto extends TransactionRequestProcessingResultDto
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $amount,
        public readonly Carbon $createdAt,
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
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt->toIso8601String(),
        ];
    }
}
