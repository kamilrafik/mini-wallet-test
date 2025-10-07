<?php

namespace App\Features\Wallet\Validators;

use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Requests\TransactionRequestDto;

final class TransactionRequestValidator
{
    public static function validate(TransactionRequestDto $dto): TransactionRequestValidationResultDto
    {
        if (bccomp(0, $dto->amount) >= 0) {
            return new TransactionRequestValidationResultDto(__('Invalid transaction amount.'));
        }

        if (Transaction::query()->where('idempotency_key', $dto->idempotencyKey)->exists()) {
            return new TransactionRequestValidationResultDto(__('Transaction has been processed already.'));
        }

        $receiver = User::query()->where('uuid', $dto->toUserUuid)->select('id')->first();
        if ($receiver === null) {
            return new TransactionRequestValidationResultDto(__('Receiver not found.'));
        }

        $sender = User::query()->where('uuid', $dto->fromUserUuid)->select('id')->first();
        if ($sender === null) {
            return new TransactionRequestValidationResultDto(__('Sender not found.'));
        }

        if (!Balance::query()->where('user_id', $receiver->id)->exists()) {
            return new TransactionRequestValidationResultDto(__('Receiver balance not found.'));
        }

        $senderBalance = Balance::query()->where('user_id', $sender->id)->select('balance')->first();
        if ($senderBalance === null) {
            return new TransactionRequestValidationResultDto(__('Sender balance not found.'));
        }

        if (bccomp($senderBalance->balance, $dto->amount) < 0) {
            return new TransactionRequestValidationResultDto(__('Sender balance is not enough.'));
        }

        return new TransactionRequestValidationResultDto();
    }
}
