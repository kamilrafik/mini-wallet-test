<?php

namespace App\Features\Wallet\Requests;

use Illuminate\Http\Request;

final class TransactionRequestDto
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
            $request->get('fromUserUuid'),
            $request->get('toUserUuid'),
            $request->get('amount')
        );
    }
}
