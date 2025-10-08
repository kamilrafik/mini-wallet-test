<?php

namespace App\Http\Controllers;

use App\Features\Wallet\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    )
    {
    }

    public function getBalance(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'balance' => $this->walletService->getBalance($request->user()->uuid),
        ]);
    }

    public function getLatestTransactions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'transactions' => $this->walletService->getLatestTransactions($request->user()->uuid)
        ]);
    }

    public function createTransaction(Request $request): JsonResponse
    {
        $result = $this->walletService->createTransactionFromUserRequest($request);

        return response()->json([
            'success' => $result->isSuccessful(),
            'result' => $result->toArray(),
        ]);
    }
}
