<?php

namespace App\Features\Wallet\Events;

use App\Features\Wallet\Dtos\Response\TransactionDetailsDto;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    public function __construct(
        private readonly string $uuid,
        private readonly string $amount,
        private readonly string $counterpartAmount,
        private readonly Carbon $createdAt,
        private readonly string $senderUuid,
        private readonly string $senderName,
        private readonly string $senderEmail,
        private readonly string $receiverUuid,
        private readonly string $receiverName,
        private readonly string $receiverEmail,
    )
    {
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('users.' . $this->receiverUuid),
            new PrivateChannel('users.' . $this->senderUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.created';
    }

    public function broadcastWith(): array
    {
        return (new TransactionDetailsDto(
            $this->uuid,
            $this->amount,
            $this->counterpartAmount,
            $this->createdAt,
            // to avoid relying on call context we pass null here, sign will be detected in frontend
            null,
            $this->senderUuid,
            $this->senderName,
            $this->senderEmail,
            $this->receiverUuid,
            $this->receiverName,

        ))->toArray();
    }
}
