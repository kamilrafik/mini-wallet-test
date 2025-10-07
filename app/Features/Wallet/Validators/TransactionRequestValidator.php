<?php

namespace App\Features\Wallet\Validators;

use App\Features\Wallet\Dtos\Request\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\Request\TransactionRequestDto;
use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Repositories\BalanceRepository;
use App\Features\Wallet\Repositories\UserRepository;
use App\Features\Wallet\Validators\Contexts\TransactionRequestValidationContext;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationFailureDto;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationResultDto;
use App\Features\Wallet\Validators\Dtos\TransactionRequestValidationSuccessDto;

final class TransactionRequestValidator
{
    private const IDEMPOTENCY_KEY_MIN_LENGTH = 36;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly BalanceRepository $balanceRepository,
    )
    {
    }

    /**
     * @param TransactionRequestDto $dto
     * @return TransactionRequestValidationFailureDto|TransactionRequestValidationSuccessDto
     */
    public function validate(TransactionRequestCommissionedDto $dto): TransactionRequestValidationResultDto
    {
        if (bccomp('0', $dto->amount) >= 0) {
            return new TransactionRequestValidationFailureDto(__('Invalid transaction amount.'));
        }

        if (bccomp('0', $dto->commissionedAmount) >= 0) {
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

        $receiver = $this->userRepository->findByUuid($dto->toUserUuid);
        if ($receiver === null) {
            return new TransactionRequestValidationFailureDto(__('Receiver not found.'));
        }

        $sender = $this->userRepository->findByUuid($dto->fromUserUuid);
        if ($sender === null) {
            return new TransactionRequestValidationFailureDto(__('Sender not found.'));
        }

        $receiverBalance = $this->balanceRepository->findByUserId($receiver->id);
        if ($receiverBalance === null) {
            return new TransactionRequestValidationFailureDto(__('Receiver balance not found.'));
        }

        $senderBalance = $this->balanceRepository->findByUserId($sender->id);
        if ($senderBalance === null) {
            return new TransactionRequestValidationFailureDto(__('Sender balance not found.'));
        }

        // later we check this balance in decrement logic
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
