<?php

namespace App\Features\Wallet\Events;

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
        return [
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt,
            'senderUuid' => $this->senderUuid,
            'senderName' => $this->senderName,
            'senderEmail' => $this->senderEmail,
            'receiverUuid' => $this->receiverUuid,
            'receiverName' => $this->receiverName,
            'receiverEmail' => $this->receiverEmail,
        ];
    }
}
