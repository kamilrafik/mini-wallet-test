<?php

namespace App\Features\Wallet\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    public function __construct(
        private readonly string $userUuid,
        private readonly string $balanceAmount,
    )
    {
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('users.' . $this->userUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'balance.updated';
    }
}
