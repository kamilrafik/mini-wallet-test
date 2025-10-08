<?php

namespace App\Features\Wallet\Services\Decorators;

use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Repositories\BalanceRepository;
use App\Features\Wallet\Repositories\UserRepository;
use App\Features\Wallet\Services\Contexts\RichTransactionCreationContext;
use App\Features\Wallet\Services\Contexts\TransactionCreationContext;

class TransactionCreationContextDecorator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly BalanceRepository $balanceRepository,
    )
    {
    }

    public function decorate(TransactionCreationContext $context): RichTransactionCreationContext
    {
        $sender = $this->userRepository->findByUuid($context->senderUuid);
        $senderBalance = $sender ? $this->balanceRepository->findByUserId($sender->id) : null;
        $receiver = $this->userRepository->findByUuid($context->receiverUuid);
        $receiverBalance = $receiver ? $this->balanceRepository->findByUserId($receiver->id) : null;

        return new RichTransactionCreationContext(
            $context->transactionUuid,
            $context->transactionAmount,
            $context->transactionCreatedAt,
            $context->senderUuid,
            $sender?->name,
            $sender?->email,
            $senderBalance?->balance,
            $context->receiverUuid,
            $receiver?->name,
            $receiver?->email,
            $receiverBalance?->balance
        );
    }
}
