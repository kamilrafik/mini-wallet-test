<?php

namespace App\Features\Wallet\Mappers;

use App\Features\Wallet\Dtos\Request\TransactionRequestDto;
use App\Features\Wallet\Models\User;
use Illuminate\Http\Request;

class UserTransactionRequestMapper
{
    public static function mapFromRequest(Request $request): TransactionRequestDto
    {
        $user = User::query()->where('email', $request->get('email'))->select('uuid')->first();
        if (!$user) {
            throw new \Exception(__('User not found by email.'));
        }

        return new TransactionRequestDto(
            $request->get('idempotencyKey'),
            $request->user()->uuid,
            $user->uuid,
            $request->get('amount')
        );
    }
}
