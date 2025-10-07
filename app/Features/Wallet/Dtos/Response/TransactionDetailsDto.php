<?php

namespace App\Features\Wallet\Dtos\Response;

use Carbon\Carbon;

final class TransactionDetailsDto
{
    public function __construct(
        private readonly string $uuid,
        private readonly string $amount,
        private readonly Carbon $createdAt,
        private readonly ?string $receiverUuid = null,
        private readonly ?string $receiverName = null,
        private readonly ?string $receiverEmail = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt->toDateTimeString(),
            'receiverUuid' => $this->receiverUuid,
            'receiverName' => $this->receiverName,
            'receiverEmail' => $this->receiverEmail,
        ];
    }
}
