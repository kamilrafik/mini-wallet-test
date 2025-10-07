<?php

namespace App\Features\Wallet\Services;

use App\Features\Wallet\Dtos\Request\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\Request\TransactionRequestDto;
use App\Features\Wallet\Dtos\Response\TransactionDetailsDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingFailureDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingResultDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingSuccessDto;
use App\Features\Wallet\Events\TransactionCreated;
use App\Features\Wallet\Factories\EventFactory;
use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Policies\TransactionCommissionPolicy;
use App\Features\Wallet\Services\Contexts\TransactionCreationContext;
use App\Features\Wallet\Validators\Contexts\TransactionRequestValidationContext;
use App\Features\Wallet\Validators\TransactionRequestValidator;
use App\Support\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class WalletService
{
    private const DEFAULT_LATEST_TRANSACTION_COUNT = 20;

    private const CACHE_LOCK_TIMEOUT = 15;

    private const BCSCALE = 2;

    private $prevBcscale = null;

    public function __construct(
        private readonly EventFactory $eventFactory,
    )
    {
    }

    protected function saveTransaction(
        TransactionRequestCommissionedDto $dto,
        TransactionRequestValidationContext $validationContext
    ): TransactionCreationContext
    {
        $context = null;
        DB::beginTransaction();
        try {
            $context = $this->performTransactionSavingOperations(
                $dto,
                $validationContext
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $context;
    }

    public function createTransaction(Request $request): TransactionRequestProcessingResultDto
    {
        $transactionCreationContext = null;

        $dto = TransactionRequestDto::fromRequest($request);

        $lockKey = implode(
            '|',
            [
                $dto->idempotencyKey,
                $dto->fromUserUuid,
                $dto->toUserUuid,
            ]
        );
        // cache lock is applied for maximum optimization to avoid processing multiple identical requests
        $lock = Cache::lock($lockKey, self::CACHE_LOCK_TIMEOUT);
        if (!$lock->get()) {
            return new TransactionRequestProcessingFailureDto(__('The request is in processing already.'));
        }

        $this->setBcscale();
        try {
            $dto = TransactionCommissionPolicy::apply($dto);

            $validationResult = TransactionRequestValidator::validate($dto);
            if (!$validationResult->isSuccessful()) {
                return new TransactionRequestProcessingFailureDto(
                    __('Validation error.'),
                    $validationResult->getErrors()
                );
            }

            $transactionCreationContext = $this->saveTransaction(
                $dto,
                $validationResult->context
            );
        } catch (\Exception $e) {
            return TransactionRequestProcessingFailureDto::fromException($e);
        } finally {
            $lock->release();
            $this->restoreBcscale();
        }

        event($this->eventFactory->transactionCreated($transactionCreationContext));

        return new TransactionRequestProcessingSuccessDto(
            $transactionCreationContext->transactionUuid,
            $transactionCreationContext->transactionAmount,
            $transactionCreationContext->transactionCreatedAt
        );
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

    private function performTransactionSavingOperations(
        TransactionRequestCommissionedDto $dto,
        TransactionRequestValidationContext $validationContext
    ): TransactionCreationContext
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

        $transaction = null;
        try {
            $transaction = Transaction::create([
                'uuid' => Uuid::generate(),
                'idempotency_key' => $dto->idempotencyKey,
                'from_balance_id' => $validationContext->senderBalanceId,
                'to_balance_id' => $validationContext->receiverBalanceId,
                'amount' => $dto->commissionedAmount,
                'commission' => $dto->commission,
            ]);
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to create transaction.'), $e->getCode(), $e);
        }

        return new TransactionCreationContext(
            $transaction->uuid,
            $transaction->amount,
            $transaction->created_at,
            $dto->fromUserUuid,
            $dto->toUserUuid,
        );
    }

    /**
     * @param $userUuid
     * @param $limit
     * @return TransactionDetailsDto[]
     * @throws \Exception
     */
    public function getLatestTransactions($userUuid, $limit = self::DEFAULT_LATEST_TRANSACTION_COUNT): array
    {
        $user = User::query()->where('uuid', $userUuid)->first();
        if ($user === null) {
            throw new \Exception(__('User not found.'));
        }

        $balance = Balance::query()->where('user_id', $user->id)->first();
        if ($balance === null) {
            throw new \Exception(__('Balance not found.'));
        }

        $transactions = Transaction::query()->where('from_balance_id', $balance->id)->orderByDesc('id')->limit($limit)->get();

        $result = [];
        foreach ($transactions as $transaction) {
            $balance = Balance::query()->where('id', $transaction->to_balance_id)->first();
            $receiver = null;
            if ($balance !== null) {
                $receiver = User::query()->where('id', $balance->user_id)->first();
            }

            $result[] = new TransactionDetailsDto(
                $transaction->uuid,
                $transaction->amount,
                $transaction->created_at,
                $receiver->uuid ?? null,
                $receiver->name ?? null,
                $receiver->email ?? null,
            );
        }

        return $result;
    }
}
