<?php

namespace App\Http\Controllers;

use App\Features\Wallet\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    )
    {
    }

    public function getLatestTransactions(Request $request)
    {
        return response()->json([
            'success' => true,
            'transactions' => $this->walletService->getLatestTransactions($request->user()->uuid)
        ]);
    }

    public function createTransaction(Request $request)
    {
        $result = $this->walletService->createTransaction($request);

        return response()->json([
            'success' => $result->isSuccessful(),
            'result' => $result->toArray(),
        ]);
    }
}
