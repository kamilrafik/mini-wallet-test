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
        private readonly string $receiverUuid,
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
}
