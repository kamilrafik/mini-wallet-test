<?php

namespace App\Features\Wallet\Dtos\Response;

use App\Features\Wallet\Dtos\Response\Enums\TransactionSign;
use Carbon\Carbon;

final class TransactionDetailsDto
{
    public function __construct(
        private readonly string $uuid,
        private readonly string $amount,
        private readonly string $counterpartAmount,
        private readonly Carbon $createdAt,
        private readonly ?TransactionSign $sign = null,
        private readonly ?string $senderUuid = null,
        private readonly ?string $senderName = null,
        private readonly ?string $senderEmail = null,
        private readonly ?string $receiverUuid = null,
        private readonly ?string $receiverName = null,
        private readonly ?string $receiverEmail = null,
    )
    {
    }

    /**
     * The structure could be more deep, like 'sender' => [] etc, but it is not important for the test task.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'counterpartAmount' => $this->counterpartAmount,
            'createdAt' => $this->createdAt->toIso8601String(),
            'sign' => $this->sign?->value,
            'senderUuid' => $this->senderUuid,
            'senderName' => $this->senderName,
            'senderEmail' => $this->senderEmail,
            'receiverUuid' => $this->receiverUuid,
            'receiverName' => $this->receiverName,
            'receiverEmail' => $this->receiverEmail,
        ];
    }
}
