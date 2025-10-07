<?php

namespace App\Features\Wallet\Validators;

use App\Features\Wallet\Logic\Dtos\TransactionRequestCommissionedDto;
use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Requests\TransactionRequestDto;
use App\Features\Wallet\Validators\Contexts\TransactionRequestValidationContext;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationFailureDto;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationResultDto;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationSuccessDto;

final class TransactionRequestValidator
{
    private const IDEMPOTENCY_KEY_MIN_LENGTH = 36;

    /**
     * @param TransactionRequestDto $dto
     * @return TransactionRequestValidationFailureDto|TransactionRequestValidationSuccessDto
     */
    public static function validate(TransactionRequestCommissionedDto $dto): TransactionRequestValidationResultDto
    {
        if (bccomp(0, $dto->amount) >= 0) {
            return new TransactionRequestValidationFailureDto(__('Invalid transaction amount.'));
        }

        if (bccomp(0, $dto->commissionedAmount) >= 0) {
            return new TransactionRequestValidationFailureDto(__('Invalid commissioned transaction amount.'));
        }

        if (bccomp($dto->amount, $dto->commissionedAmount) < 0) {
            return new TransactionRequestValidationFailureDto(__('Commissioned amount turned out to be less than original amount.'));
        }

        if (strlen($dto->idempotencyKey) < self::IDEMPOTENCY_KEY_MIN_LENGTH) {
            return new TransactionRequestValidationFailureDto(__('Invalid transaction idempotency key.'));
        }

        if (Transaction::query()->where('idempotency_key', $dto->idempotencyKey)->exists()) {
            return new TransactionRequestValidationFailureDto(__('Transaction has been processed already.'));
        }

        $receiver = User::query()->where('uuid', $dto->toUserUuid)->select('id')->first();
        if ($receiver === null) {
            return new TransactionRequestValidationFailureDto(__('Receiver not found.'));
        }

        $sender = User::query()->where('uuid', $dto->fromUserUuid)->select('id')->first();
        if ($sender === null) {
            return new TransactionRequestValidationFailureDto(__('Sender not found.'));
        }

        $receiverBalance = Balance::query()->where('user_id', $receiver->id)->first();
        if ($receiverBalance === null) {
            return new TransactionRequestValidationFailureDto(__('Receiver balance not found.'));
        }

        $senderBalance = Balance::query()->where('user_id', $sender->id)->first();
        if ($senderBalance === null) {
            return new TransactionRequestValidationFailureDto(__('Sender balance not found.'));
        }

        if (bccomp($senderBalance->balance, $dto->commissionedAmount) < 0) {
            return new TransactionRequestValidationFailureDto(__('Sender balance is not enough.'));
        }

        $context = new TransactionRequestValidationContext(
            $senderBalance->user_id,
            $senderBalance->id,
            $senderBalance->balance,
            $receiverBalance->user_id,
            $receiverBalance->id,
        );

        return new TransactionRequestValidationSuccessDto($context);
    }
}
