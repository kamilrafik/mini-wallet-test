<?php

namespace App\Features\Wallet\Dtos\Request;

use App\Features\Wallet\Support\BcMath;
use Illuminate\Http\Request;

class TransactionRequestDto
{
    public readonly string $amount;

    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $fromUserUuid,
        public readonly string $toUserUuid,
        string $amount
    )
    {
        $this->amount = (new BcMath())->round($amount);
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->get('idempotencyKey'),
            $request->user()->uuid,
            $request->get('toUserUuid'),
            (new BcMath())->round($request->get('amount'))
        );
    }
}
