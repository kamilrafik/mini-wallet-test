<?php

namespace App\Features\Wallet\Services;

use App\Features\Wallet\Dtos\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\TransactionRequestDto;
use App\Features\Wallet\Dtos\TransactionRequestProcessingResultDto;
use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Policies\TransactionRequestPolicy;
use App\Features\Wallet\Validators\Contexts\TransactionRequestValidationContext;
use App\Features\Wallet\Validators\TransactionRequestValidator;
use App\Support\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class WalletService
{
    private const BCSCALE = 2;

    private $prevBcscale = null;

    public function processTransactionRequest(Request $request): TransactionRequestProcessingResultDto
    {
        $this->setBcscale();

        DB::beginTransaction();
        try {
            $dto = TransactionRequestDto::fromRequest($request);

            $dto = TransactionRequestPolicy::apply($dto, self::BCSCALE);

            $validationResult = TransactionRequestValidator::validate($dto);
            if (!$validationResult->isSuccessful()) {
                return new TransactionRequestProcessingResultDto(
                    __('Validation error.'),
                    $validationResult->getErrors()
                );
            }

            $this->saveTransaction(
                $dto,
                $validationResult->context
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return TransactionRequestProcessingResultDto::fromException($e);
        } finally {
            $this->restoreBcscale();
        }

        return new TransactionRequestProcessingResultDto();
    }

    private function setBcscale()
    {
        $this->prevBcscale = bcscale(self::BCSCALE);
    }

    private function restoreBcscale()
    {
        bcscale($this->prevBcscale);
        $this->prevBcscale = null;
    }

    private function saveTransaction(
        TransactionRequestCommissionedDto $dto,
        TransactionRequestValidationContext $validationContext
    )
    {
        if (!Balance::query()
            ->where([
                'id' => $validationContext->senderBalanceId,
                'balance' => $validationContext->senderBalanceAmount
            ])
            ->update(['balance' => bcsub($validationContext->senderBalanceAmount, $dto->amount)])
        ) {
            throw new \Exception(__('Failed to subtract amount from sender\'s balance.'));
        }

        if (!Balance::query()
            ->where([
                'id' => $validationContext->receiverBalanceId,
            ])
            ->increment('balance', $dto->amount)
        ) {
            throw new \Exception(__('Failed to add amount into receiver\'s balance.'));
        }

        try {
            Transaction::create([
                'uuid' => Uuid::generate(),
                'idempotency_key' => $dto->idempotencyKey,
                'from_balance_id' => $validationContext->senderBalanceId,
                'to_balance_id' => $validationContext->receiverBalanceId,
                'amount' => $dto->commissionedAmount,
                'commission' => bcsub($dto->commissionedAmount, $dto->amount),
            ]);
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to create transaction.'), $e->getCode(), $e);
        }
    }
}
