<?php

namespace App\Features\Wallet\Dispatchers;

use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingSuccessDto;
use App\Features\Wallet\Events\BalanceUpdated;
use App\Features\Wallet\Events\TransactionCreated;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Services\Contexts\RichTransactionCreationContext;
use App\Features\Wallet\Services\Contexts\TransactionCreationContext;

class EventDispatcher
{
    public function dispatchTransactionCreated(RichTransactionCreationContext $context): void
    {
        event(new TransactionCreated(
            $context->transactionUuid,
            $context->transactionAmount,
            $context->transactionCounterpartAmount,
            $context->transactionCreatedAt,
            $context->senderUuid,
            $context->senderName,
            $context->senderEmail,
            $context->receiverUuid,
            $context->receiverName,
            $context->receiverEmail,
        ));

        event(new BalanceUpdated(
            $context->senderUuid,
            $context->senderBalance,
        ));

        event(new BalanceUpdated(
            $context->receiverUuid,
            $context->receiverBalance,
        ));
    }
}
