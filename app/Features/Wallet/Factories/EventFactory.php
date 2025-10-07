<?php

namespace App\Features\Wallet\Factories;

use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingSuccessDto;
use App\Features\Wallet\Events\TransactionCreated;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Services\Contexts\TransactionCreationContext;

class EventFactory
{
    public function transactionCreated(TransactionCreationContext $context): TransactionCreated
    {
        return new TransactionCreated(
            $context->transactionUuid,
            $context->transactionAmount,
            $context->transactionCreatedAt,
            $context->senderUuid,
            $context->receiverUuid,
        );
    }
}
