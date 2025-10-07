<?php

namespace App\Features\Wallet\Dtos\Request;

use Illuminate\Http\Request;

class TransactionRequestDto
{
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $fromUserUuid,
        public readonly string $toUserUuid,
        public readonly string $amount
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->get('idempotencyKey'),
            $request->user()->uuid,
            $request->get('toUserUuid'),
            $request->get('amount')
        );
    }
}
