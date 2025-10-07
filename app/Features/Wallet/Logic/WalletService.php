<?php

namespace App\Features\Wallet\Logic;

use App\Features\Wallet\Models\Transaction;
use App\Features\Wallet\Requests\TransactionRequestDto;
use App\Features\Wallet\Validators\TransactionRequestValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class WalletService
{
    private const BCSCALE = 2;

    private static $prevBcscale = null;

    public static function processTransactionRequest(Request $request): TransactionRequestProcessingResultDto
    {
        self::setBcscale();
        DB::beginTransaction();
        try {
            $dto = TransactionRequestDto::fromRequest($request);
            $dto = TransactionPolicy::apply($dto);
            $validationResult = TransactionRequestValidator::validate($dto);
            if (!$validationResult->isSuccessful()) {
                return new TransactionRequestProcessingResultDto(
                    __('Validation error.'),
                    $validationResult->getErrors()
                );
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return TransactionRequestProcessingResultDto::fromException($e);
        } finally {
            self::restoreBcscale();
        }

        return new TransactionRequestProcessingResultDto();
    }

    private static function setBcscale()
    {
        self::$prevBcscale = bcscale(self::BCSCALE);
    }

    private static function restoreBcscale()
    {
        bcscale(self::$prevBcscale);
        self::$prevBcscale = null;
    }
}
