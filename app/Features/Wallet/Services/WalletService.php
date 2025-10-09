<?php

namespace App\Features\Wallet\Services;

use App\Features\Wallet\Dtos\Request\TransactionRequestCommissionedDto;
use App\Features\Wallet\Dtos\Request\TransactionRequestDto;
use App\Features\Wallet\Dtos\Response\Enums\TransactionSign;
use App\Features\Wallet\Dtos\Response\TransactionDetailsDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingFailureDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingResultDto;
use App\Features\Wallet\Dtos\Response\TransactionRequestProcessingSuccessDto;
use App\Features\Wallet\Dispatchers\EventDispatcher;
use App\Features\Wallet\Mappers\UserTransactionRequestMapper;
use App\Features\Wallet\Models\Balance;
use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Models\User;
use App\Features\Wallet\Policies\TransactionCommissionPolicy;
use App\Features\Wallet\Repositories\BalanceRepository;
use App\Features\Wallet\Repositories\UserRepository;
use App\Features\Wallet\Services\Contexts\RichTransactionCreationContext;
use App\Features\Wallet\Services\Contexts\TransactionCreationContext;
use App\Features\Wallet\Services\Decorators\TransactionCreationContextDecorator;
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
        private readonly TransactionRequestValidator $transactionRequestValidator,
        private readonly EventDispatcher $eventDispatcher,
        private readonly TransactionCreationContextDecorator $transactionCreationContextDecorator,
        private readonly BalanceRepository $balanceRepository,
        private readonly UserRepository $userRepository,
    )
    {
    }

    protected function saveTransaction(
        TransactionRequestCommissionedDto $dto,
        TransactionRequestValidationContext $validationContext
    ): RichTransactionCreationContext
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

        // we implement enrichment necessary for events/UI separately to perform in db transaction only the minimal set of operations
        return $this->transactionCreationContextDecorator->decorate($context);
    }

    public function createTransactionFromRequest(Request $request): TransactionRequestProcessingResultDto
    {
        $dto = TransactionRequestDto::fromRequest($request);
        return $this->createTransaction($dto);
    }

    public function createTransactionFromUserRequest(Request $request): TransactionRequestProcessingResultDto
    {
        try {
            $dto = UserTransactionRequestMapper::mapFromRequest($request);
        } catch (\Exception $e) {
            // not using fromException() because set of possible transformation failures is predictable
            return new TransactionRequestProcessingFailureDto(
                $e->getMessage(),
                null,
                $e
            );
        }

        return $this->createTransaction($dto);
    }

    protected function createTransaction(TransactionRequestDto $dto): TransactionRequestProcessingResultDto
    {
        $transactionCreationContext = null;

        // cache lock is applied for maximum optimization to avoid processing multiple identical requests
        $lockKey = implode(
            '|',
            [
                $dto->idempotencyKey,
                $dto->fromUserUuid,
                $dto->toUserUuid,
            ]
        );
        $lock = Cache::lock($lockKey, self::CACHE_LOCK_TIMEOUT);
        if (!$lock->get()) {
            return new TransactionRequestProcessingFailureDto(__('The request is in processing already.'));
        }

        // such project-level setting is enough for test task; in production project it may be done in separate classes that are specific for each kind of operation
        $this->setBcscale();
        try {
            $dto = TransactionCommissionPolicy::apply($dto);

            $validationResult = $this->transactionRequestValidator->validate($dto);
            if (!$validationResult->isSuccessful()) {
                return new TransactionRequestProcessingFailureDto(
                    __('Validation error.'),
                    $validationResult->getErrors()
                );
            }

            // note that we separate maximum of logic from db transaction;
            // optimistic concurrency control is used
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

        $this->eventDispatcher->dispatchTransactionCreated($transactionCreationContext);

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

    /**
     * Data operations are not moved into repository classes because they are extremely important and unique, but not something generic.
     *
     * @param TransactionRequestCommissionedDto $dto
     * @param TransactionRequestValidationContext $validationContext
     * @return TransactionCreationContext
     * @throws \Exception
     */
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
            ->decrement('balance', $dto->commissionedAmount)
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
                'counterpart_amount' => $dto->amount,
            ]);
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to create transaction.'), (int) $e->getCode(), $e);
        }

        return new TransactionCreationContext(
            $transaction->uuid,
            $transaction->amount,
            $transaction->counterpart_amount,
            $transaction->created_at,
            $dto->fromUserUuid,
            $dto->toUserUuid,
        );
    }

    /**
     * The method by itself just gives the simplest implementation of the task requirement to list transactions (I consciously skipped implementation of pagination).
     *
     * @param $userUuid
     * @param $limit
     * @return TransactionDetailsDto[]
     * @throws \Exception
     */
    public function getLatestTransactions($userUuid, $limit = self::DEFAULT_LATEST_TRANSACTION_COUNT): array
    {
        $user = $this->userRepository->findByUuid($userUuid);
        if ($user === null) {
            throw new \Exception(__('User not found.'));
        }

        $balance = $this->balanceRepository->findByUserId($user->id);
        if ($balance === null) {
            throw new \Exception(__('Balance not found.'));
        }

        $transactions = Transaction::query()
            ->where('from_balance_id', $balance->id)
            ->orWhere('to_balance_id', $balance->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $result = [];
        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            // no reasons to cache anything here; it should be done either on storage level or on presentation level for old transactions
            $senderBalance = $this->balanceRepository->findById($transaction->from_balance_id);
            $sender = $this->userRepository->findById($senderBalance->user_id);
            $receiverBalance = $this->balanceRepository->findById($transaction->to_balance_id);
            $receiver = $this->userRepository->findById($receiverBalance->user_id);

            $result[] = new TransactionDetailsDto(
                $transaction->uuid,
                $transaction->amount,
                $transaction->counterpart_amount,
                $transaction->created_at,
                ($balance->id === $transaction->to_balance_id) ? TransactionSign::Plus : TransactionSign::Minus,
                $sender?->uuid,
                $sender?->name,
                $sender?->email,
                $receiver?->uuid,
                $receiver?->name,
                $receiver?->email,
            );
        }

        return $result;
    }

    public function getBalance($userUuid): ?string
    {
        $user = $this->userRepository->findByUuid($userUuid);
        if (!$user) {
            return null;
        }

        return $this->balanceRepository->findByUserId($user->id)?->balance;
    }
}
